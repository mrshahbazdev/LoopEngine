<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessTemplate;
use App\Models\TemplateRating;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = ProcessTemplate::with(['sharedBy'])
            ->where('is_public', true)
            ->withCount('ratings');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                  ->orWhere('name_de', 'like', "%{$search}%")
                  ->orWhere('description_en', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort', 'newest');
        $templates = match ($sortBy) {
            'popular' => $query->orderByDesc('install_count')->paginate(12),
            'rating' => $query->orderByDesc('rating')->paginate(12),
            default => $query->latest()->paginate(12),
        };

        $categories = ProcessTemplate::where('is_public', true)
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        return view('templates.index', compact('templates', 'categories', 'sortBy'));
    }

    public function show(ProcessTemplate $template)
    {
        $template->load(['sharedBy', 'process.steps', 'ratings.user']);
        $userRating = null;
        if (auth()->check()) {
            $userRating = TemplateRating::where('template_id', $template->id)
                ->where('user_id', auth()->id())
                ->first();
        }

        return view('templates.show', compact('template', 'userRating'));
    }

    public function share(Request $request)
    {
        $processes = Process::where('created_by', $request->user()->id)
            ->where('status', 'active')
            ->whereDoesntHave('template')
            ->get();

        return view('templates.share', compact('processes'));
    }

    public function storeShare(Request $request)
    {
        $validated = $request->validate([
            'process_id' => ['required', 'exists:processes,id'],
            'name_en' => ['required', 'string', 'max:255'],
            'name_de' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_de' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'string'],
        ]);

        $process = Process::where('id', $validated['process_id'])
            ->where('created_by', $request->user()->id)
            ->firstOrFail();

        ProcessTemplate::create([
            'process_id' => $process->id,
            'shared_by' => $request->user()->id,
            'name_en' => $validated['name_en'],
            'name_de' => $validated['name_de'],
            'description_en' => $validated['description_en'],
            'description_de' => $validated['description_de'],
            'category' => $validated['category'],
            'tags' => $validated['tags'] ? array_map('trim', explode(',', $validated['tags'])) : null,
        ]);

        return redirect()->route('templates.index')->with('success', __('app.template_shared'));
    }

    public function install(Request $request, ProcessTemplate $template)
    {
        $sourceProcess = $template->process;
        $clone = $sourceProcess->duplicate($request->user());

        $template->increment('install_count');

        return redirect()->route('processes.edit', $clone)->with('success', __('app.template_installed'));
    }

    public function rate(Request $request, ProcessTemplate $template)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:1000'],
        ]);

        TemplateRating::updateOrCreate(
            ['template_id' => $template->id, 'user_id' => $request->user()->id],
            $validated,
        );

        $template->recalculateRating();

        return back()->with('success', __('app.rating_saved'));
    }

    public function export(ProcessTemplate $template)
    {
        $process = $template->process->load(['steps.options', 'steps.transitions']);

        $export = [
            'loopengine_version' => '1.0',
            'template' => [
                'name_en' => $template->name_en,
                'name_de' => $template->name_de,
                'description_en' => $template->description_en,
                'description_de' => $template->description_de,
                'category' => $template->category,
                'tags' => $template->tags,
            ],
            'process' => [
                'name_en' => $process->name_en,
                'name_de' => $process->name_de,
                'description_en' => $process->description_en,
                'description_de' => $process->description_de,
                'category' => $process->category,
            ],
            'steps' => $process->steps->map(fn ($step) => [
                'order' => $step->order,
                'question_en' => $step->question_en,
                'question_de' => $step->question_de,
                'help_text_en' => $step->help_text_en,
                'help_text_de' => $step->help_text_de,
                'step_type' => $step->step_type,
                'is_loop_checkpoint' => $step->is_loop_checkpoint,
                'max_loops' => $step->max_loops,
                'options' => $step->options->map(fn ($o) => [
                    'label_en' => $o->label_en,
                    'label_de' => $o->label_de,
                    'value' => $o->value,
                    'color' => $o->color,
                ]),
                'transitions' => $step->transitions->map(fn ($t) => [
                    'option_index' => $step->options->search(fn ($o) => $o->id === $t->option_id),
                    'action_type' => $t->action_type,
                    'target_step_order' => $t->target_step_id ? $process->steps->firstWhere('id', $t->target_step_id)?->order : null,
                ]),
            ]),
        ];

        $filename = str_replace(' ', '_', $template->name_en) . '.json';

        return response()->json($export)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}
