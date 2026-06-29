<x-layouts.app :title="__('app.run_summary')">
    <x-slot:header>{{ __('app.run_summary') }}: {{ $run->process->localizedName() }}</x-slot:header>

    <div class="space-y-6">
        {{-- Summary Stats --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6 card-hover transition-colors animate-fade-in stagger-1">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('app.status') }}</dt>
                <dd class="mt-1 text-lg font-semibold">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                        {{ $run->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                           ($run->status === 'cancelled' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400') }}">
                        {{ __('app.run_status_' . $run->status) }}
                    </span>
                </dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6 card-hover transition-colors animate-fade-in stagger-2">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('app.loop_count') }}</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">{{ $run->loop_count }}</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6 card-hover transition-colors animate-fade-in stagger-3">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('app.started_at') }}</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $run->started_at->format('M d, Y H:i') }}</dd>
            </div>
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6 card-hover transition-colors animate-fade-in stagger-4">
                <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('app.completed_at') }}</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $run->completed_at?->format('M d, Y H:i') ?? '-' }}</dd>
            </div>
        </div>

        {{-- Responses --}}
        <div class="overflow-hidden bg-white dark:bg-gray-800 shadow sm:rounded-lg transition-colors animate-slide-in-up stagger-1">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">{{ __('app.runs') }} - {{ __('app.details') }}</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($run->responses->sortBy('created_at') as $response)
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $response->step->localizedQuestion() }}</p>
                                <p class="mt-1 text-sm text-indigo-600 dark:text-indigo-400">
                                    @if($response->option)
                                        {{ $response->option->localizedLabel() }}
                                    @else
                                        {{ $response->response_text }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $response->responded_at->format('H:i:s') }}</p>
                                @if($response->loop_iteration > 1)
                                    <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-2 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-400">
                                        {{ __('app.loop_iteration') }} {{ $response->loop_iteration }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Audit Trail --}}
        <div class="overflow-hidden bg-white dark:bg-gray-800 shadow sm:rounded-lg transition-colors animate-slide-in-up stagger-2">
            <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-5 sm:px-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">{{ __('app.audit_trail') }}</h3>
            </div>
            <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($run->logs->sortBy('created_at') as $log)
                    <li class="px-4 py-3 sm:px-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $log->action === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                       ($log->action === 'looped_back' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                       ($log->action === 'started' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                                       ($log->action === 'answered' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'))) }}">
                                    {{ __('app.action_' . $log->action, [], 'en') }}
                                </span>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $log->user->name }}</span>
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->format('H:i:s') }}</span>
                        </div>
                        @if($log->details)
                            <div class="mt-1 ml-20 text-xs text-gray-500 dark:text-gray-400">
                                @foreach($log->details as $key => $value)
                                    <span class="mr-3">{{ $key }}: {{ is_string($value) ? $value : json_encode($value) }}</span>
                                @endforeach
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('runs.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">&larr; {{ __('app.back') }}</a>
            <div class="flex items-center gap-2">
                <a href="{{ route('export.run.csv', $run) }}" class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 btn-press transition-colors">
                    {{ __('app.export') }} CSV
                </a>
                <a href="{{ route('export.run.pdf', $run) }}" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 btn-press transition-colors">
                    {{ __('app.export') }} PDF
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
