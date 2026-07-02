<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\RunLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function analytics(Request $request)
    {
        $companyId = $request->user()->company_id;

        $totalProcesses = Process::count();
        $activeProcesses = Process::where('status', 'active')->count();
        $totalRuns = ProcessRun::whereHas('process', fn ($q) => $q->where('company_id', $companyId))->count();
        $completedRuns = ProcessRun::whereHas('process', fn ($q) => $q->where('company_id', $companyId))->where('status', 'completed')->count();
        $completionRate = $totalRuns > 0 ? round(($completedRuns / $totalRuns) * 100, 1) : 0;
        $avgLoops = ProcessRun::whereHas('process', fn ($q) => $q->where('company_id', $companyId))->where('status', 'completed')->avg('loop_count') ?? 0;

        $processStats = Process::withCount([
            'runs',
            'runs as completed_runs_count' => function ($q) {
                $q->where('status', 'completed');
            },
        ])->withAvg('runs', 'loop_count')
          ->where('status', 'active')
          ->get();

        $teamPerformance = User::where('company_id', $companyId)
            ->withCount([
                'processRuns',
                'processRuns as completed_runs_count' => function ($q) {
                    $q->where('status', 'completed');
                },
            ])->get()
          ->filter(fn ($u) => $u->process_runs_count > 0)
          ->values();

        return view('admin.analytics', compact(
            'totalProcesses',
            'activeProcesses',
            'totalRuns',
            'completedRuns',
            'completionRate',
            'avgLoops',
            'processStats',
            'teamPerformance',
        ));
    }

    public function auditLog(Request $request)
    {
        $companyId = $request->user()->company_id;

        $query = RunLog::with(['run.process', 'user'])
            ->whereHas('run.process', fn ($q) => $q->where('company_id', $companyId))
            ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->paginate(25);
        $users = User::where('company_id', $companyId)->orderBy('name')->get();

        return view('admin.audit', compact('logs', 'users'));
    }

    public function users(Request $request)
    {
        $users = User::where('company_id', $request->user()->company_id)
            ->withCount('processRuns')
            ->orderBy('name')
            ->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,team_lead,employee'],
        ]);

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'company_id' => $request->user()->company_id,
        ]);

        return back()->with('success', __('app.user_saved'));
    }

    public function updateUser(Request $request, User $user)
    {
        if ($user->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'role' => ['required', 'in:admin,team_lead,employee'],
        ]);

        $user->update($validated);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return back()->with('success', __('app.user_saved'));
    }

    public function destroyUser(Request $request, User $user)
    {
        if ($user->company_id !== $request->user()->company_id) {
            abort(403);
        }

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Cannot delete yourself.');
        }

        $user->delete();
        return back()->with('success', __('app.user_deleted'));
    }
}
