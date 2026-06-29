<?php

namespace App\Services;

use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\ProcessStep;
use App\Models\RunLog;
use App\Models\RunResponse;
use App\Models\StepOption;
use App\Models\StepTransition;
use App\Models\User;
use App\Notifications\ProcessRunCompleted;

class ProcessEngine
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function startRun(Process $process, User $user): ProcessRun
    {
        $firstStep = $process->firstStep();

        $run = ProcessRun::create([
            'process_id' => $process->id,
            'started_by' => $user->id,
            'status' => 'in_progress',
            'current_step_id' => $firstStep?->id,
            'loop_count' => 0,
            'started_at' => now(),
        ]);

        $this->log($run, $user, 'started', [
            'process_name' => $process->name_en,
            'first_step' => $firstStep?->question_en,
        ]);

        $this->webhookService->dispatch('run.started', [
            'run_id' => $run->id,
            'process' => $process->name_en,
            'user' => $user->name,
        ]);

        return $run;
    }

    public function submitAnswer(ProcessRun $run, ProcessStep $step, ?StepOption $option, ?string $text, User $user): array
    {
        $loopIteration = $this->getCurrentLoopIteration($run, $step);

        RunResponse::create([
            'run_id' => $run->id,
            'step_id' => $step->id,
            'option_id' => $option?->id,
            'response_text' => $text,
            'responded_by' => $user->id,
            'loop_iteration' => $loopIteration,
            'responded_at' => now(),
        ]);

        $this->log($run, $user, 'answered', [
            'step' => $step->question_en,
            'answer' => $option?->label_en ?? $text,
            'loop_iteration' => $loopIteration,
        ]);

        return $this->resolveTransition($run, $step, $option, $user);
    }

    public function resolveTransition(ProcessRun $run, ProcessStep $step, ?StepOption $option, User $user): array
    {
        $transition = null;

        if ($option) {
            $transition = StepTransition::where('step_id', $step->id)
                ->where('option_id', $option->id)
                ->first();
        }

        if (!$transition) {
            $transition = StepTransition::where('step_id', $step->id)
                ->whereNull('option_id')
                ->first();
        }

        if (!$transition) {
            return $this->moveToNextStep($run, $step, $user);
        }

        return match ($transition->action_type) {
            'next_step' => $this->moveToNextStep($run, $step, $user),
            'goto_step' => $this->goToStep($run, $transition->target_step_id, $user),
            'loop_back' => $this->loopBack($run, $step, $transition, $user),
            'start_process' => $this->startSubProcess($run, $transition->target_process_id, $user),
            'end' => $this->endRun($run, $user),
            default => $this->moveToNextStep($run, $step, $user),
        };
    }

    protected function moveToNextStep(ProcessRun $run, ProcessStep $currentStep, User $user): array
    {
        $nextStep = ProcessStep::where('process_id', $run->process_id)
            ->where('order', '>', $currentStep->order)
            ->orderBy('order')
            ->first();

        if (!$nextStep) {
            return $this->endRun($run, $user);
        }

        $run->update(['current_step_id' => $nextStep->id]);

        if ($nextStep->step_type === 'end') {
            return $this->endRun($run, $user);
        }

        return [
            'action' => 'next_step',
            'step' => $nextStep,
            'run' => $run->fresh(),
        ];
    }

    protected function goToStep(ProcessRun $run, int $targetStepId, User $user): array
    {
        $step = ProcessStep::findOrFail($targetStepId);
        $run->update(['current_step_id' => $step->id]);

        return [
            'action' => 'goto_step',
            'step' => $step,
            'run' => $run->fresh(),
        ];
    }

    protected function loopBack(ProcessRun $run, ProcessStep $currentStep, StepTransition $transition, User $user): array
    {
        $targetStepId = $transition->target_step_id;

        if (!$targetStepId) {
            $firstStep = ProcessStep::where('process_id', $run->process_id)
                ->orderBy('order')
                ->first();
            $targetStepId = $firstStep?->id;
        }

        if ($currentStep->max_loops > 0) {
            $loopCount = $this->getStepLoopCount($run, $currentStep);
            if ($loopCount >= $currentStep->max_loops) {
                $this->log($run, $user, 'max_loops_reached', [
                    'step' => $currentStep->question_en,
                    'max_loops' => $currentStep->max_loops,
                    'actual_loops' => $loopCount,
                ]);

                return $this->moveToNextStep($run, $currentStep, $user);
            }
        }

        $run->update([
            'current_step_id' => $targetStepId,
            'loop_count' => $run->loop_count + 1,
        ]);

        $this->log($run, $user, 'looped_back', [
            'from_step' => $currentStep->question_en,
            'to_step_id' => $targetStepId,
            'loop_count' => $run->loop_count,
        ]);

        $this->webhookService->dispatch('run.looped_back', [
            'run_id' => $run->id,
            'loop_count' => $run->loop_count,
        ]);

        $targetStep = ProcessStep::findOrFail($targetStepId);

        return [
            'action' => 'loop_back',
            'step' => $targetStep,
            'run' => $run->fresh(),
            'loop_count' => $run->loop_count,
        ];
    }

    protected function startSubProcess(ProcessRun $run, int $processId, User $user): array
    {
        $process = Process::findOrFail($processId);
        $subRun = $this->startRun($process, $user);

        $this->log($run, $user, 'started_subprocess', [
            'sub_process' => $process->name_en,
            'sub_run_id' => $subRun->id,
        ]);

        return [
            'action' => 'start_process',
            'run' => $subRun,
            'step' => $subRun->currentStep,
            'parent_run' => $run,
        ];
    }

    public function endRun(ProcessRun $run, User $user): array
    {
        $run->update([
            'status' => 'completed',
            'completed_at' => now(),
            'current_step_id' => null,
        ]);

        $this->log($run, $user, 'completed', [
            'total_loops' => $run->loop_count,
            'duration_minutes' => $run->started_at->diffInMinutes(now()),
        ]);

        // Notify the process creator and the user who ran it
        $run->load('process.creator');
        $creator = $run->process->creator;
        if ($creator && $creator->id !== $user->id) {
            $creator->notify(new ProcessRunCompleted($run));
        }
        $user->notify(new ProcessRunCompleted($run));

        // Update team assignment status if applicable
        $assignment = \App\Models\TeamAssignment::where('process_id', $run->process_id)
            ->where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->first();
        if ($assignment) {
            $assignment->update(['status' => 'completed', 'completed_at' => now()]);
        }

        $this->webhookService->dispatch('run.completed', [
            'run_id' => $run->id,
            'process' => $run->process->name_en,
            'user' => $user->name,
            'loop_count' => $run->loop_count,
        ]);

        return [
            'action' => 'end',
            'run' => $run->fresh(),
        ];
    }

    public function pauseRun(ProcessRun $run, User $user): ProcessRun
    {
        $run->update(['status' => 'paused']);
        $this->log($run, $user, 'paused');
        $this->webhookService->dispatch('run.paused', ['run_id' => $run->id]);
        return $run->fresh();
    }

    public function resumeRun(ProcessRun $run, User $user): ProcessRun
    {
        $run->update(['status' => 'in_progress']);
        $this->log($run, $user, 'resumed');
        return $run->fresh();
    }

    public function cancelRun(ProcessRun $run, User $user): ProcessRun
    {
        $run->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);
        $this->log($run, $user, 'cancelled');
        $this->webhookService->dispatch('run.cancelled', ['run_id' => $run->id]);
        return $run->fresh();
    }

    protected function getCurrentLoopIteration(ProcessRun $run, ProcessStep $step): int
    {
        $lastResponse = RunResponse::where('run_id', $run->id)
            ->where('step_id', $step->id)
            ->orderByDesc('loop_iteration')
            ->first();

        return $lastResponse ? $lastResponse->loop_iteration + 1 : 1;
    }

    protected function getStepLoopCount(ProcessRun $run, ProcessStep $step): int
    {
        return RunResponse::where('run_id', $run->id)
            ->where('step_id', $step->id)
            ->count();
    }

    protected function log(ProcessRun $run, User $user, string $action, array $details = []): RunLog
    {
        return RunLog::create([
            'run_id' => $run->id,
            'user_id' => $user->id,
            'action' => $action,
            'details' => $details ?: null,
        ]);
    }
}
