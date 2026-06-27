<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessStep;
use App\Models\StepOption;
use App\Models\StepTransition;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index(Request $request)
    {
        $query = Process::with('creator')->withCount(['steps', 'runs']);

        if (!$request->user()->isAdmin()) {
            $query->where(function ($q) use ($request) {
                $q->where('created_by', $request->user()->id)
                  ->orWhere('status', 'active');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_de', 'like', "%{$search}%");
            });
        }

        $processes = $query->latest()->paginate(12);

        return view('processes.index', compact('processes'));
    }

    public function create()
    {
        return view('processes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_de' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_de' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $process = Process::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'status' => 'draft',
            'version' => 1,
        ]);

        return redirect()->route('processes.edit', $process)
            ->with('success', __('app.process_saved'));
    }

    public function show(Process $process)
    {
        $process->load(['steps.options.transition', 'creator', 'runs']);
        return view('processes.show', compact('process'));
    }

    public function edit(Process $process)
    {
        $this->authorizeProcess($process);
        $process->load(['steps.options.transition', 'steps.transitions']);
        $allProcesses = Process::where('id', '!=', $process->id)
            ->where('status', 'active')
            ->get();
        return view('processes.edit', compact('process', 'allProcesses'));
    }

    public function update(Request $request, Process $process)
    {
        $this->authorizeProcess($process);

        $validated = $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_de' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_de' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $process->update($validated);

        return redirect()->route('processes.edit', $process)
            ->with('success', __('app.process_saved'));
    }

    public function destroy(Process $process)
    {
        $this->authorizeProcess($process);
        $process->delete();
        return redirect()->route('processes.index')
            ->with('success', __('app.process_deleted'));
    }

    public function activate(Process $process)
    {
        $this->authorizeProcess($process);
        $process->update(['status' => 'active']);
        return back()->with('success', __('app.process_activated'));
    }

    public function archive(Process $process)
    {
        $this->authorizeProcess($process);
        $process->update(['status' => 'archived']);
        return back()->with('success', __('app.process_archived'));
    }

    // Step management
    public function storeStep(Request $request, Process $process)
    {
        $this->authorizeProcess($process);

        $validated = $request->validate([
            'question_en' => ['required', 'string'],
            'question_de' => ['nullable', 'string'],
            'help_text_en' => ['nullable', 'string'],
            'help_text_de' => ['nullable', 'string'],
            'step_type' => ['required', 'in:question,decision,loop_check,info,end'],
            'is_loop_checkpoint' => ['boolean'],
            'is_required' => ['boolean'],
            'max_loops' => ['integer', 'min:0'],
        ]);

        $maxOrder = $process->steps()->max('order') ?? -1;

        $process->steps()->create([
            ...$validated,
            'order' => $maxOrder + 1,
            'is_loop_checkpoint' => $request->boolean('is_loop_checkpoint'),
            'is_required' => $request->boolean('is_required', true),
            'max_loops' => $validated['max_loops'] ?? 0,
        ]);

        return back()->with('success', __('app.process_saved'));
    }

    public function updateStep(Request $request, Process $process, ProcessStep $step)
    {
        $this->authorizeProcess($process);

        $validated = $request->validate([
            'question_en' => ['required', 'string'],
            'question_de' => ['nullable', 'string'],
            'help_text_en' => ['nullable', 'string'],
            'help_text_de' => ['nullable', 'string'],
            'step_type' => ['required', 'in:question,decision,loop_check,info,end'],
            'is_loop_checkpoint' => ['boolean'],
            'is_required' => ['boolean'],
            'max_loops' => ['integer', 'min:0'],
        ]);

        $step->update([
            ...$validated,
            'is_loop_checkpoint' => $request->boolean('is_loop_checkpoint'),
            'is_required' => $request->boolean('is_required', true),
            'max_loops' => $validated['max_loops'] ?? 0,
        ]);

        return back()->with('success', __('app.process_saved'));
    }

    public function destroyStep(Process $process, ProcessStep $step)
    {
        $this->authorizeProcess($process);
        $step->delete();
        return back()->with('success', __('app.process_saved'));
    }

    public function reorderSteps(Request $request, Process $process)
    {
        $this->authorizeProcess($process);

        $validated = $request->validate([
            'steps' => ['required', 'array'],
            'steps.*' => ['integer', 'exists:process_steps,id'],
        ]);

        foreach ($validated['steps'] as $order => $stepId) {
            ProcessStep::where('id', $stepId)
                ->where('process_id', $process->id)
                ->update(['order' => $order]);
        }

        return response()->json(['success' => true]);
    }

    // Option management
    public function storeOption(Request $request, ProcessStep $step)
    {
        $validated = $request->validate([
            'label_en' => ['required', 'string', 'max:255'],
            'label_de' => ['nullable', 'string', 'max:255'],
            'value' => ['required', 'string', 'max:100'],
            'color' => ['nullable', 'in:green,red,yellow,blue'],
        ]);

        $maxOrder = $step->options()->max('order') ?? -1;

        $step->options()->create([
            ...$validated,
            'order' => $maxOrder + 1,
        ]);

        return back()->with('success', __('app.process_saved'));
    }

    public function destroyOption(StepOption $option)
    {
        $option->delete();
        return back()->with('success', __('app.process_saved'));
    }

    // Transition management
    public function storeTransition(Request $request, ProcessStep $step)
    {
        $validated = $request->validate([
            'option_id' => ['nullable', 'exists:step_options,id'],
            'action_type' => ['required', 'in:next_step,goto_step,start_process,loop_back,end'],
            'target_step_id' => ['nullable', 'exists:process_steps,id'],
            'target_process_id' => ['nullable', 'exists:processes,id'],
        ]);

        $step->transitions()->create($validated);

        return back()->with('success', __('app.process_saved'));
    }

    public function destroyTransition(StepTransition $transition)
    {
        $transition->delete();
        return back()->with('success', __('app.process_saved'));
    }

    protected function authorizeProcess(Process $process): void
    {
        $user = request()->user();
        if (!$user->isAdmin() && $process->created_by !== $user->id) {
            abort(403);
        }
    }
}
