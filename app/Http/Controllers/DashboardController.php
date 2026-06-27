<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessRun;
use App\Models\RunLog;
use Illuminate\Http\Request;

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

        return view('dashboard.index', compact(
            'activeProcesses',
            'myRuns',
            'completedRuns',
            'totalLoops',
            'recentActivity',
            'availableProcesses',
        ));
    }
}
