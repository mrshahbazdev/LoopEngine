<x-layouts.app :title="__('app.assignments')">
    <x-slot:header>{{ __('app.assignments') }}</x-slot:header>

    <div class="mb-6 flex items-center justify-between animate-fade-in">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('app.assignments') }}</h1>
        @if(auth()->user()->canManageProcesses())
            <a href="{{ route('team.assign-form') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                {{ __('app.assign_process') }}
            </a>
        @endif
    </div>

    <div class="rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-slide-in-up">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('app.process') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('app.assigned_to') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('app.assigned_by_label') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('app.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('app.notes') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('app.timestamp') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500 dark:text-gray-400">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($assignments as $assignment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                <a href="{{ route('processes.show', $assignment->process) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                    {{ $assignment->process->localizedName() }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $assignment->user->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $assignment->assigner->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $assignment->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                                       ($assignment->status === 'in_progress' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400') }}">
                                    {{ __('app.assignment_status_' . $assignment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $assignment->notes ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $assignment->assigned_at->format('M d, Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @if($assignment->status === 'pending' && $assignment->user_id === auth()->id())
                                        <form action="{{ route('runs.start', $assignment->process) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">{{ __('app.start_run') }}</button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->canManageProcesses())
                                        <form action="{{ route('team.remove-assignment', $assignment) }}" method="POST"
                                              onsubmit="return confirm('{{ __('app.confirm_remove_assignment') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs transition-colors">{{ __('app.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">{{ __('app.no_assignments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $assignments->links() }}
        </div>
    </div>
</x-layouts.app>
