<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\StepOption;
use App\Services\ProcessEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcessApiController extends Controller
{
    public function __construct(
        protected ProcessEngine $engine,
    ) {}

    public function listProcesses(Request $request): JsonResponse
    {
        $processes = Process::where('status', 'active')
            ->withCount('steps')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->localizedName(),
                'description' => $p->localizedDescription(),
                'category' => $p->category,
                'version' => $p->version,
                'steps_count' => $p->steps_count,
            ]);

        return response()->json(['data' => $processes]);
    }

    public function showProcess(Process $process): JsonResponse
    {
        $process->load(['steps.options', 'creator']);

        return response()->json([
            'data' => [
                'id' => $process->id,
                'name' => $process->localizedName(),
                'description' => $process->localizedDescription(),
                'status' => $process->status,
                'version' => $process->version,
                'category' => $process->category,
                'creator' => $process->creator->name,
                'steps' => $process->steps->map(fn ($s) => [
                    'id' => $s->id,
                    'order' => $s->order,
                    'question' => $s->localizedQuestion(),
                    'help_text' => $s->localizedHelpText(),
                    'type' => $s->step_type,
                    'is_loop_checkpoint' => $s->is_loop_checkpoint,
                    'max_loops' => $s->max_loops,
                    'options' => $s->options->map(fn ($o) => [
                        'id' => $o->id,
                        'label' => $o->localizedLabel(),
                        'value' => $o->value,
                        'color' => $o->color,
                    ]),
                ]),
            ],
        ]);
    }

    public function startRun(Request $request, Process $process): JsonResponse
    {
        if (!$process->isActive()) {
            return response()->json(['error' => 'Process is not active.'], 422);
        }

        if ($process->steps()->count() === 0) {
            return response()->json(['error' => 'Process has no steps.'], 422);
        }

        $run = $this->engine->startRun($process, $request->user());
        $run->load('currentStep.options');

        return response()->json([
            'data' => $this->formatRun($run),
        ], 201);
    }

    public function listRuns(Request $request): JsonResponse
    {
        $runs = ProcessRun::with(['process'])
            ->where('started_by', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $runs->map(fn ($r) => [
                'id' => $r->id,
                'process' => $r->process->localizedName(),
                'status' => $r->status,
                'loop_count' => $r->loop_count,
                'started_at' => $r->started_at->toISOString(),
                'completed_at' => $r->completed_at?->toISOString(),
            ]),
            'meta' => [
                'current_page' => $runs->currentPage(),
                'last_page' => $runs->lastPage(),
                'total' => $runs->total(),
            ],
        ]);
    }

    public function showRun(ProcessRun $run): JsonResponse
    {
        $run->load(['process', 'currentStep.options', 'responses.step', 'responses.option']);

        return response()->json([
            'data' => $this->formatRun($run),
        ]);
    }

    public function submitAnswer(Request $request, ProcessRun $run): JsonResponse
    {
        if ($run->started_by !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$run->isInProgress()) {
            return response()->json(['error' => 'Run is not in progress.'], 422);
        }

        $validated = $request->validate([
            'option_id' => ['nullable', 'exists:step_options,id'],
            'response_text' => ['nullable', 'string'],
        ]);

        $step = $run->currentStep;
        $option = !empty($validated['option_id']) ? StepOption::find($validated['option_id']) : null;

        $result = $this->engine->submitAnswer(
            $run,
            $step,
            $option,
            $validated['response_text'] ?? null,
            $request->user(),
        );

        $run->refresh();
        $run->load(['currentStep.options']);

        return response()->json([
            'data' => $this->formatRun($run),
            'action' => $result['action'],
        ]);
    }

    public function pauseRun(Request $request, ProcessRun $run): JsonResponse
    {
        $this->engine->pauseRun($run, $request->user());
        return response()->json(['data' => $this->formatRun($run->fresh())]);
    }

    public function resumeRun(Request $request, ProcessRun $run): JsonResponse
    {
        $this->engine->resumeRun($run, $request->user());
        $run = $run->fresh();
        $run->load('currentStep.options');
        return response()->json(['data' => $this->formatRun($run)]);
    }

    public function cancelRun(Request $request, ProcessRun $run): JsonResponse
    {
        $this->engine->cancelRun($run, $request->user());
        return response()->json(['data' => $this->formatRun($run->fresh())]);
    }

    public function runSummary(ProcessRun $run): JsonResponse
    {
        $run->load(['process', 'responses.step', 'responses.option', 'logs.user']);

        return response()->json([
            'data' => [
                'id' => $run->id,
                'process' => $run->process->localizedName(),
                'status' => $run->status,
                'loop_count' => $run->loop_count,
                'started_at' => $run->started_at->toISOString(),
                'completed_at' => $run->completed_at?->toISOString(),
                'responses' => $run->responses->sortBy('created_at')->values()->map(fn ($r) => [
                    'step' => $r->step->localizedQuestion(),
                    'answer' => $r->option ? $r->option->localizedLabel() : $r->response_text,
                    'loop_iteration' => $r->loop_iteration,
                    'responded_at' => $r->responded_at->toISOString(),
                ]),
                'audit_trail' => $run->logs->sortBy('created_at')->values()->map(fn ($l) => [
                    'action' => $l->action,
                    'user' => $l->user->name,
                    'details' => $l->details,
                    'timestamp' => $l->created_at->toISOString(),
                ]),
            ],
        ]);
    }

    protected function formatRun(ProcessRun $run): array
    {
        return [
            'id' => $run->id,
            'process' => $run->process?->localizedName(),
            'status' => $run->status,
            'loop_count' => $run->loop_count,
            'started_at' => $run->started_at->toISOString(),
            'completed_at' => $run->completed_at?->toISOString(),
            'current_step' => $run->currentStep ? [
                'id' => $run->currentStep->id,
                'order' => $run->currentStep->order,
                'question' => $run->currentStep->localizedQuestion(),
                'help_text' => $run->currentStep->localizedHelpText(),
                'type' => $run->currentStep->step_type,
                'is_loop_checkpoint' => $run->currentStep->is_loop_checkpoint,
                'options' => $run->currentStep->options->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->localizedLabel(),
                    'value' => $o->value,
                    'color' => $o->color,
                ]),
            ] : null,
        ];
    }
}
