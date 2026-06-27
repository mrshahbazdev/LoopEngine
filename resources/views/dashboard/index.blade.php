<x-layouts.app :title="__('app.dashboard')">
    <x-slot:header>{{ __('app.welcome_back', ['name' => auth()->user()->name]) }}</x-slot:header>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.active_processes') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $activeProcesses }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.my_runs') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $myRuns->count() }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.completed_runs') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $completedRuns }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.total_loops') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $totalLoops }}</dd>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
        {{-- Active Runs --}}
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">{{ __('app.my_runs') }}</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($myRuns as $run)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-indigo-600">{{ $run->process->localizedName() }}</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ __('app.step') }}: {{ $run->currentStep?->localizedQuestion() ?? '-' }}
                                </p>
                                <p class="mt-1 text-xs text-gray-400">
                                    {{ __('app.loop_count') }}: {{ $run->loop_count }}
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $run->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ __('app.run_status_' . $run->status) }}
                                </span>
                                @if($run->status === 'in_progress')
                                    <a href="{{ route('runs.execute', $run) }}" class="rounded bg-indigo-600 px-2 py-1 text-xs font-semibold text-white hover:bg-indigo-500">
                                        {{ __('app.continue_run') }}
                                    </a>
                                @elseif($run->status === 'paused')
                                    <form method="POST" action="{{ route('runs.resume', $run) }}">
                                        @csrf
                                        <button type="submit" class="rounded bg-green-600 px-2 py-1 text-xs font-semibold text-white hover:bg-green-500">
                                            {{ __('app.resume_run') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500">{{ __('app.no_runs') }}</li>
                @endforelse
            </ul>
        </div>

        {{-- Quick Start --}}
        <div class="overflow-hidden rounded-lg bg-white shadow">
            <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">{{ __('app.quick_start') }}</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200">
                @forelse($availableProcesses as $process)
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $process->localizedName() }}</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ $process->steps_count }} {{ __('app.steps') }} &middot;
                                    {{ __('app.created_by') }}: {{ $process->creator->name }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('runs.start', $process) }}">
                                @csrf
                                <button type="submit" class="rounded bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                                    {{ __('app.start_run') }}
                                </button>
                            </form>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-8 text-center text-sm text-gray-500">{{ __('app.no_active_processes') }}</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Recent Activity --}}
    @if($recentActivity->isNotEmpty())
    <div class="mt-8 overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900">{{ __('app.recent_activity') }}</h3>
        </div>
        <ul role="list" class="divide-y divide-gray-200">
            @foreach($recentActivity as $log)
                <li class="px-4 py-3 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                {{ $log->action === 'completed' ? 'bg-green-100 text-green-800' :
                                   ($log->action === 'looped_back' ? 'bg-yellow-100 text-yellow-800' :
                                   ($log->action === 'started' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ __('app.action_' . $log->action) }}
                            </span>
                            <span class="text-sm text-gray-700">{{ $log->run?->process?->localizedName() }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</x-layouts.app>
