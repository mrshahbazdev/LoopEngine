<x-layouts.app :title="__('app.runs')">
    <x-slot:header>{{ __('app.runs') }}</x-slot:header>

    <div class="mb-4">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="rounded-md border-0 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                <option value="">{{ __('app.status') }}</option>
                @foreach(['in_progress','completed','paused','failed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ __('app.run_status_' . $s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 btn-press transition-colors">{{ __('app.search') }}</button>
        </form>
    </div>

    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow sm:rounded-lg transition-colors animate-fade-in">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.process') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.status') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.loop_count') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 hidden sm:table-cell">{{ __('app.started_at') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200 hidden md:table-cell">{{ __('app.completed_at') }}</th>
                        <th class="relative py-3.5 pl-3 pr-4"><span class="sr-only">{{ __('app.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($runs as $run)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white">
                                {{ $run->process->localizedName() }}
                                @if(!auth()->user()->isAdmin())
                                @else
                                    <br><span class="text-xs text-gray-400 dark:text-gray-500">{{ $run->starter->name }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $run->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                       ($run->status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' :
                                       ($run->status === 'paused' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                       ($run->status === 'cancelled' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400'))) }}">
                                    {{ __('app.run_status_' . $run->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $run->loop_count }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden sm:table-cell">{{ $run->started_at->format('M d, H:i') }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400 hidden md:table-cell">{{ $run->completed_at?->format('M d, H:i') ?? '-' }}</td>
                            <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                                @if($run->status === 'in_progress')
                                    <a href="{{ route('runs.execute', $run) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors">{{ __('app.continue_run') }}</a>
                                @elseif($run->status === 'completed')
                                    <a href="{{ route('runs.summary', $run) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors">{{ __('app.run_summary') }}</a>
                                @elseif($run->status === 'paused')
                                    <form method="POST" action="{{ route('runs.resume', $run) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300 transition-colors">{{ __('app.resume_run') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_runs') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $runs->links() }}</div>
</x-layouts.app>
