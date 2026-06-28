<x-layouts.app :title="__('app.audit_log')">
    <x-slot:header>{{ __('app.audit_log') }}</x-slot:header>

    <div class="mb-4 animate-fade-in">
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="action" class="rounded-md border-0 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                <option value="">{{ __('app.actions') }}</option>
                @foreach(['started','answered','looped_back','completed','paused','resumed','cancelled','failed'] as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ __('app.action_' . $action, [], 'en') }}</option>
                @endforeach
            </select>
            <select name="user_id" class="rounded-md border-0 py-1.5 text-gray-900 dark:text-white dark:bg-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                <option value="">{{ __('app.users') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-white dark:bg-gray-700 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 btn-press transition-colors">{{ __('app.search') }}</button>
        </form>
        <div class="flex items-center gap-2 mt-3">
            <a href="{{ route('export.audit.csv', request()->query()) }}" class="rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 btn-press transition-colors">
                {{ __('app.export') }} CSV
            </a>
            <a href="{{ route('export.audit.pdf', request()->query()) }}" class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 btn-press transition-colors">
                {{ __('app.export') }} PDF
            </a>
        </div>
    </div>

    <div class="overflow-hidden bg-white dark:bg-gray-800 shadow sm:rounded-lg transition-colors animate-slide-in-up">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.timestamp') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.performed_by') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.process') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.actions') }}</th>
                        <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-200">{{ __('app.details') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 dark:text-gray-400">{{ $log->created_at->format('M d, H:i:s') }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900 dark:text-white">{{ $log->user->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $log->run?->process?->localizedName() }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $log->action === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                       ($log->action === 'looped_back' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                       ($log->action === 'started' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')) }}">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-3 py-4 text-xs text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                @if($log->details)
                                    {{ json_encode($log->details) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_results') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.app>
