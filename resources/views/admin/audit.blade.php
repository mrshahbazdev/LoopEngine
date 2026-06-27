<x-layouts.app :title="__('app.audit_log')">
    <x-slot:header>{{ __('app.audit_log') }}</x-slot:header>

    <div class="mb-4">
        <form method="GET" class="flex gap-3">
            <select name="action" class="rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                <option value="">{{ __('app.actions') }}</option>
                @foreach(['started','answered','looped_back','completed','paused','resumed','cancelled','failed'] as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ __('app.action_' . $action, [], 'en') }}</option>
                @endforeach
            </select>
            <select name="user_id" class="rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                <option value="">{{ __('app.users') }}</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('app.search') }}</button>
        </form>
    </div>

    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">{{ __('app.timestamp') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.performed_by') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.process') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.actions') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.details') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500">{{ $log->created_at->format('M d, H:i:s') }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ $log->user->name }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $log->run?->process?->localizedName() }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $log->action === 'completed' ? 'bg-green-100 text-green-800' :
                                   ($log->action === 'looped_back' ? 'bg-yellow-100 text-yellow-800' :
                                   ($log->action === 'started' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-3 py-4 text-xs text-gray-500 max-w-xs truncate">
                            @if($log->details)
                                {{ json_encode($log->details) }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-sm text-gray-500">{{ __('app.no_results') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>
</x-layouts.app>
