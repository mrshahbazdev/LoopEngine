<x-layouts.app :title="__('app.team_dashboard')">
    <x-slot:header>{{ __('app.team_dashboard') }}</x-slot:header>

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('app.team_dashboard') }}</h1>
        @if(auth()->user()->canManageProcesses())
            <a href="{{ route('team.assign-form') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                {{ __('app.assign_process') }}
            </a>
        @endif
    </div>

    {{-- Team Members --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($teamMembers as $member)
            <div class="rounded-lg bg-white p-5 shadow">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-600">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">{{ $member->name }}</div>
                        <div class="text-xs text-gray-500">{{ __('app.role_' . $member->role) }}</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="rounded bg-gray-50 p-2">
                        <div class="text-lg font-bold text-gray-900">{{ $member->process_runs_count }}</div>
                        <div class="text-xs text-gray-500">{{ __('app.total_runs') }}</div>
                    </div>
                    <div class="rounded bg-green-50 p-2">
                        <div class="text-lg font-bold text-green-600">{{ $member->completed_runs_count }}</div>
                        <div class="text-xs text-gray-500">{{ __('app.completed_runs') }}</div>
                    </div>
                    <div class="rounded bg-blue-50 p-2">
                        <div class="text-lg font-bold text-blue-600">{{ $member->assignments_count }}</div>
                        <div class="text-xs text-gray-500">{{ __('app.assignments') }}</div>
                    </div>
                    <div class="rounded bg-yellow-50 p-2">
                        <div class="text-lg font-bold text-yellow-600">{{ $member->pending_assignments_count }}</div>
                        <div class="text-xs text-gray-500">{{ __('app.pending') }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Recent Assignments --}}
    <div class="rounded-lg bg-white shadow">
        <div class="border-b px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('app.recent_assignments') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.process') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.assigned_to') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.assigned_by_label') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500">{{ __('app.timestamp') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentAssignments as $assignment)
                        <tr>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $assignment->process->localizedName() }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $assignment->user->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600">{{ $assignment->assigner->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $assignment->status === 'completed' ? 'bg-green-100 text-green-800' :
                                       ($assignment->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ __('app.assignment_status_' . $assignment->status) }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $assignment->assigned_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">{{ __('app.no_assignments') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
