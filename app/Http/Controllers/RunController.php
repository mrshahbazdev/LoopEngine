<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\ProcessStep;
use App\Models\StepOption;
use App\Services\ProcessEngine;
use Illuminate\Http\Request;

class RunController extends Controller
{
    public function __construct(
        protected ProcessEngine $engine,
    ) {}

    public function index(Request $request)
    {
        $query = ProcessRun::with(['process', 'starter', 'currentStep'])
            ->latest();

        if (!$request->user()->isAdmin()) {
            $query->where('started_by', $request->user()->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $runs = $query->paginate(15);

        return view('runs.index', compact('runs'));
    }

    public function start(Request $request, Process $process)
    {
        if (!$process->isActive()) {
            return back()->with('error', 'Process is not active.');
        }

        if ($process->steps()->count() === 0) {
            return back()->with('error', 'Process has no steps.');
        }

        $run = $this->engine->startRun($process, $request->user());

        return redirect()->route('runs.execute', $run);
    }

    public function execute(Request $request, ProcessRun $run)
    {
        if ($run->started_by !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        if ($run->isCompleted()) {
            return redirect()->route('runs.summary', $run);
        }

        if ($run->isPaused()) {
            return view('runs.paused', compact('run'));
        }

        $step = $run->currentStep;

        if (!$step) {
            return redirect()->route('runs.summary', $run);
        }

        $step->load('options');
        $run->load('process');

        $progress = $this->calculateProgress($run);

        return view('runs.execute', compact('run', 'step', 'progress'));
    }

    public function answer(Request $request, ProcessRun $run)
    {
        if ($run->started_by !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        if (!$run->isInProgress()) {
            return redirect()->route('runs.execute', $run);
        }

        $step = $run->currentStep;

        $validated = $request->validate([
            'option_id' => ['nullable', 'exists:step_options,id'],
            'response_text' => ['nullable', 'string'],
        ]);

        $option = null;
        if (!empty($validated['option_id'])) {
            $option = StepOption::find($validated['option_id']);
        }

        $result = $this->engine->submitAnswer(
            $run,
            $step,
            $option,
            $validated['response_text'] ?? null,
            $request->user(),
        );

        if ($result['action'] === 'end') {
            return redirect()->route('runs.summary', $run)
                ->with('success', __('app.run_completed'));
        }

        if ($result['action'] === 'loop_back') {
            return redirect()->route('runs.execute', $run)
                ->with('info', __('app.loop_back_notice'));
        }

        if ($result['action'] === 'start_process') {
            return redirect()->route('runs.execute', $result['run']);
        }

        return redirect()->route('runs.execute', $run);
    }

    public function pause(Request $request, ProcessRun $run)
    {
        $this->engine->pauseRun($run, $request->user());
        return redirect()->route('runs.index')
            ->with('success', __('app.run_paused'));
    }

    public function resume(Request $request, ProcessRun $run)
    {
        $this->engine->resumeRun($run, $request->user());
        return redirect()->route('runs.execute', $run)
            ->with('success', __('app.run_resumed'));
    }

    public function cancel(Request $request, ProcessRun $run)
    {
        $this->engine->cancelRun($run, $request->user());
        return redirect()->route('runs.index')
            ->with('success', __('app.run_cancelled'));
    }

    public function summary(Request $request, ProcessRun $run)
    {
        if ($run->started_by !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $run->load([
            'process',
            'responses.step',
            'responses.option',
            'responses.responder',
            'logs.user',
        ]);

        return view('runs.summary', compact('run'));
    }

    protected function calculateProgress(ProcessRun $run): array
    {
        $totalSteps = ProcessStep::where('process_id', $run->process_id)->count();
        $currentOrder = $run->currentStep?->order ?? 0;
        $percentage = $totalSteps > 0 ? round(($currentOrder / $totalSteps) * 100) : 0;

        return [
            'current' => $currentOrder + 1,
            'total' => $totalSteps,
            'percentage' => min($percentage, 100),
        ];
    }
}
