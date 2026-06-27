<x-layouts.app :title="__('app.runs')">
    <x-slot:header>{{ __('app.runs') }}</x-slot:header>

    <div class="mb-4">
        <form method="GET" class="flex gap-3">
            <select name="status" class="rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                <option value="">{{ __('app.status') }}</option>
                @foreach(['in_progress','completed','paused','failed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ __('app.run_status_' . $s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('app.search') }}</button>
        </form>
    </div>

    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">{{ __('app.process') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.status') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.loop_count') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.started_at') }}</th>
                    <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">{{ __('app.completed_at') }}</th>
                    <th class="relative py-3.5 pl-3 pr-4"><span class="sr-only">{{ __('app.actions') }}</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($runs as $run)
                    <tr>
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">
                            {{ $run->process->localizedName() }}
                            @if(!auth()->user()->isAdmin())
                            @else
                                <br><span class="text-xs text-gray-400">{{ $run->starter->name }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ $run->status === 'completed' ? 'bg-green-100 text-green-800' :
                                   ($run->status === 'in_progress' ? 'bg-blue-100 text-blue-800' :
                                   ($run->status === 'paused' ? 'bg-yellow-100 text-yellow-800' :
                                   ($run->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800'))) }}">
                                {{ __('app.run_status_' . $run->status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $run->loop_count }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $run->started_at->format('M d, H:i') }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $run->completed_at?->format('M d, H:i') ?? '-' }}</td>
                        <td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium">
                            @if($run->status === 'in_progress')
                                <a href="{{ route('runs.execute', $run) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('app.continue_run') }}</a>
                            @elseif($run->status === 'completed')
                                <a href="{{ route('runs.summary', $run) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('app.run_summary') }}</a>
                            @elseif($run->status === 'paused')
                                <form method="POST" action="{{ route('runs.resume', $run) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900">{{ __('app.resume_run') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-sm text-gray-500">{{ __('app.no_runs') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $runs->links() }}</div>
</x-layouts.app>
