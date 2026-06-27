<x-layouts.app :title="__('app.assignments')">
    <x-slot:header>{{ __('app.assignments') }}</x-slot:header>

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('app.assignments') }}</h1>
        @if(auth()->user()->canManageProcesses())
            <a href="{{ route('team.assign-form') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ __('app.assign_process') }}
            </a>
        @endif
    </div>

    <div class="rounded-lg bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.process') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.assigned_to') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.assigned_by_label') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.notes') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.timestamp') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($assignments as $assignment)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                <a href="{{ route('processes.show', $assignment->process) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $assignment->process->localizedName() }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $assignment->user->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $assignment->assigner->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $assignment->status === 'completed' ? 'bg-green-100 text-green-800' :
                                       ($assignment->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ __('app.assignment_status_' . $assignment->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $assignment->notes ?? '-' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $assignment->assigned_at->format('M d, Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm">
                                <div class="flex items-center gap-2">
                                    @if($assignment->status === 'pending' && $assignment->user_id === auth()->id())
                                        <form action="{{ route('runs.start', $assignment->process) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-indigo-600 hover:text-indigo-800">{{ __('app.start_run') }}</button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->canManageProcesses())
                                        <form action="{{ route('team.remove-assignment', $assignment) }}" method="POST"
                                              onsubmit="return confirm('{{ __('app.confirm_remove_assignment') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-xs">{{ __('app.delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">{{ __('app.no_assignments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $assignments->links() }}
        </div>
    </div>
</x-layouts.app>
