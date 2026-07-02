<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\TeamAssignment;
use App\Models\User;
use App\Notifications\ProcessAssigned;
use App\Services\WebhookService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function __construct(
        protected WebhookService $webhookService,
    ) {}

    public function assignments(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;

        if ($user->isAdmin()) {
            $assignments = TeamAssignment::with(['process', 'user', 'assigner'])
                ->whereHas('user', fn ($q) => $q->where('company_id', $companyId))
                ->latest()
                ->paginate(20);
        } elseif ($user->isTeamLead()) {
            $processIds = Process::where('created_by', $user->id)->pluck('id');
            $assignments = TeamAssignment::with(['process', 'user', 'assigner'])
                ->whereIn('process_id', $processIds)
                ->latest()
                ->paginate(20);
        } else {
            $assignments = TeamAssignment::with(['process', 'user', 'assigner'])
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(20);
        }

        return view('team.assignments', compact('assignments'));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'process_id' => ['required', 'exists:processes,id'],
            'user_id' => ['required', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $process = Process::findOrFail($validated['process_id']);
        $assignedUser = User::find($validated['user_id']);

        if ($assignedUser->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $assignment = TeamAssignment::create([
            'process_id' => $validated['process_id'],
            'user_id' => $validated['user_id'],
            'assigned_by' => $request->user()->id,
            'assigned_at' => now(),
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        $assignedUser->notify(new ProcessAssigned($assignment));

        $this->webhookService->dispatch('assignment.created', [
            'process' => $process->name_en,
            'user' => $assignedUser->name,
            'assigned_by' => $request->user()->name,
        ]);

        return back()->with('success', __('app.assignment_created'));
    }

    public function removeAssignment(TeamAssignment $assignment)
    {
        $assignment->delete();
        return back()->with('success', __('app.assignment_removed'));
    }

    public function assignForm(Request $request)
    {
        $processes = Process::where('status', 'active')->get();
        $users = User::where('company_id', $request->user()->company_id)->orderBy('name')->get();
        return view('team.assign-form', compact('processes', 'users'));
    }

    public function teamDashboard(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;

        if ($user->isAdmin()) {
            $teamMembers = User::where('company_id', $companyId)
                ->withCount([
                    'processRuns',
                    'processRuns as completed_runs_count' => fn ($q) => $q->where('status', 'completed'),
                    'assignments',
                    'pendingAssignments',
                ])->get();
        } elseif ($user->isTeamLead()) {
            $teamMembers = User::where('company_id', $companyId)
                ->where('team', $user->team)
                ->withCount([
                    'processRuns',
                    'processRuns as completed_runs_count' => fn ($q) => $q->where('status', 'completed'),
                    'assignments',
                    'pendingAssignments',
                ])->get();
        } else {
            $teamMembers = collect([$user->loadCount([
                'processRuns',
                'processRuns as completed_runs_count' => fn ($q) => $q->where('status', 'completed'),
                'assignments',
                'pendingAssignments',
            ])]);
        }

        $recentAssignments = TeamAssignment::with(['process', 'user', 'assigner'])
            ->whereHas('user', fn ($q) => $q->where('company_id', $companyId))
            ->when(!$user->isAdmin(), function ($q) use ($user) {
                if ($user->isTeamLead()) {
                    $processIds = Process::where('created_by', $user->id)->pluck('id');
                    $q->whereIn('process_id', $processIds);
                } else {
                    $q->where('user_id', $user->id);
                }
            })
            ->latest()
            ->limit(10)
            ->get();

        return view('team.dashboard', compact('teamMembers', 'recentAssignments'));
    }
}
