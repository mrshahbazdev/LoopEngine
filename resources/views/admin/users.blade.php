<x-layouts.app :title="__('app.users')">
    <x-slot:header>{{ __('app.user_management') }}</x-slot:header>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {{-- Add User Form --}}
        <div class="lg:col-span-1">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('app.add_user') }}</h3>
                <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.name') }}</label>
                        <input type="text" name="name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.email') }}</label>
                        <input type="email" name="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.password') }}</label>
                        <input type="password" name="password" required minlength="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('app.role') }}</label>
                        <select name="role" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="employee">{{ __('app.role_employee') }}</option>
                            <option value="team_lead">{{ __('app.role_team_lead') }}</option>
                            <option value="admin">{{ __('app.role_admin') }}</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('app.create') }}</button>
                </form>
            </div>
        </div>

        {{-- Users List --}}
        <div class="lg:col-span-2">
            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">{{ __('app.name') }}</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.email') }}</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.role') }}</th>
                            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.runs') }}</th>
                            <th class="relative py-3.5 pl-3 pr-4"><span class="sr-only">{{ __('app.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($users as $user)
                            <tr x-data="{ editing: false }">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                                    <template x-if="!editing">
                                        <span>{{ $user->name }}</span>
                                    </template>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                        {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' :
                                           ($user->role === 'team_lead' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ __('app.role_' . $user->role) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $user->process_runs_count }}</td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm">
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Delete user?')" class="text-red-600 hover:text-red-900">{{ __('app.delete') }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $users->links() }}</div>
        </div>
    </div>
</x-layouts.app>
