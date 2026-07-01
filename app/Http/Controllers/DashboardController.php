<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\RunLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $activeProcesses = Process::where('status', 'active')->count();

        $myRuns = ProcessRun::where('started_by', $user->id)
            ->whereIn('status', ['in_progress', 'paused'])
            ->with(['process', 'currentStep'])
            ->latest()
            ->get();

        $completedRuns = ProcessRun::where('started_by', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalLoops = ProcessRun::where('started_by', $user->id)->sum('loop_count');

        $recentActivity = RunLog::where('user_id', $user->id)
            ->with(['run.process'])
            ->latest()
            ->take(10)
            ->get();

        $availableProcesses = Process::where('status', 'active')
            ->with('creator')
            ->withCount('steps')
            ->latest()
            ->get();

        // Chart data: runs per day (last 30 days)
        $runsPerDay = ProcessRun::where('started_at', '>=', now()->subDays(30))
            ->when(!$user->isAdmin(), fn ($q) => $q->where('started_by', $user->id))
            ->selectRaw("DATE(started_at) as date, COUNT(*) as count, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Chart data: completion rate by process
        $processStats = Process::where('status', 'active')
            ->withCount([
                'runs',
                'runs as completed_runs_count' => fn ($q) => $q->where('status', 'completed'),
            ])
            ->get()
            ->filter(fn ($p) => $p->runs_count > 0)
            ->map(fn ($p) => [
                'name' => $p->localizedName(),
                'total' => $p->runs_count,
                'completed' => $p->completed_runs_count,
                'rate' => round(($p->completed_runs_count / $p->runs_count) * 100, 1),
            ])
            ->values();

        // Chart data: loop distribution
        $loopDistribution = ProcessRun::where('status', 'completed')
            ->when(!$user->isAdmin(), fn ($q) => $q->where('started_by', $user->id))
            ->selectRaw("CASE WHEN loop_count = 0 THEN '0' WHEN loop_count BETWEEN 1 AND 2 THEN '1-2' WHEN loop_count BETWEEN 3 AND 5 THEN '3-5' ELSE '6+' END as range, COUNT(*) as count")
            ->groupBy('range')
            ->get()
            ->pluck('count', 'range')
            ->toArray();

        // Chart data: status breakdown
        $statusBreakdown = ProcessRun::when(!$user->isAdmin(), fn ($q) => $q->where('started_by', $user->id))
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('dashboard.index', compact(
            'activeProcesses',
            'myRuns',
            'completedRuns',
            'totalLoops',
            'recentActivity',
            'availableProcesses',
            'runsPerDay',
            'processStats',
            'loopDistribution',
            'statusBreakdown',
        ));
    }
}
