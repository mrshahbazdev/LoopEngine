<x-layouts.app :title="__('app.webhook_logs')">
    <x-slot:header>{{ __('app.webhook_logs') }}: {{ $webhook->name }}</x-slot:header>

    <div class="mb-4">
        <a href="{{ route('webhooks.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">&larr; {{ __('app.back_to_webhooks') }}</a>
    </div>

    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-slide-in-up">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.event') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.status') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.response_code') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.timestamp') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            <span class="rounded bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs">{{ $log->event }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($log->success)
                                <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-400">{{ __('app.success') }}</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-400">{{ __('app.failed') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $log->response_code ?: '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_logs') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $logs->links() }}</div>
</x-layouts.app>
