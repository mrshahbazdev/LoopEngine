<x-layouts.app :title="__('app.permissions')">
    <x-slot:header>{{ __('app.manage_permissions') }}</x-slot:header>

    <p class="mb-6 text-sm text-gray-600 dark:text-gray-400 animate-fade-in">{{ __('app.permissions_desc') }}</p>

    <div class="overflow-x-auto rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-slide-in-up">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="sticky left-0 z-10 bg-gray-50 dark:bg-gray-900 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.user') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.role') }}</th>
                    @foreach($availablePermissions as $key => $label)
                        <th class="px-3 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400 whitespace-nowrap" title="{{ $label }}">
                            {{ Str::after($key, '.') }}
                        </th>
                    @endforeach
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <form method="POST" action="{{ route('admin.permissions.update', $user) }}">
                            @csrf @method('PUT')
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $user->role === 'team_lead' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            @php $userPerms = $user->permissions->pluck('permission')->toArray(); @endphp
                            @foreach($availablePermissions as $key => $label)
                                <td class="px-3 py-4 text-center">
                                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700"
                                        {{ in_array($key, $userPerms) ? 'checked' : '' }}
                                        title="{{ $label }}">
                                </td>
                            @endforeach
                            <td class="px-6 py-4 text-right">
                                <button type="submit" class="rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                                    {{ __('app.save') }}
                                </button>
                            </td>
                        </form>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
</x-layouts.app>
