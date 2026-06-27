<x-layouts.app :title="__('app.analytics')">
    <x-slot:header>{{ __('app.analytics') }}</x-slot:header>

    {{-- Overview Stats --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.total_processes') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $totalProcesses }}</dd>
            <dd class="mt-1 text-xs text-gray-400">{{ $activeProcesses }} {{ __('app.process_status_active') }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.total_runs') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $totalRuns }}</dd>
            <dd class="mt-1 text-xs text-gray-400">{{ $completedRuns }} {{ __('app.run_status_completed') }}</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.completion_rate') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $completionRate }}%</dd>
        </div>
        <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
            <dt class="truncate text-sm font-medium text-gray-500">{{ __('app.avg_loops') }}</dt>
            <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ round($avgLoops, 1) }}</dd>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
        {{-- Process Stats --}}
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">{{ __('app.process_stats') }}</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 pl-4 pr-3 text-left text-xs font-medium text-gray-500">{{ __('app.process') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500">{{ __('app.runs') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500">{{ __('app.completed_runs') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500">{{ __('app.avg_loops') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($processStats as $ps)
                        <tr>
                            <td class="py-3 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $ps->localizedName() }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500">{{ $ps->runs_count }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500">{{ $ps->completed_runs_count }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500">{{ round($ps->runs_avg_loop_count ?? 0, 1) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Team Performance --}}
        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
            <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">{{ __('app.team_performance') }}</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-300">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 pl-4 pr-3 text-left text-xs font-medium text-gray-500">{{ __('app.name') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500">{{ __('app.role') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500">{{ __('app.total_runs') }}</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500">{{ __('app.completed_runs') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($teamPerformance as $user)
                        <tr>
                            <td class="py-3 pl-4 pr-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500">{{ __('app.role_' . $user->role) }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500">{{ $user->process_runs_count }}</td>
                            <td class="px-3 py-3 text-sm text-gray-500">{{ $user->completed_runs_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
