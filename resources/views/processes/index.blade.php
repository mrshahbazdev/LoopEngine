<x-layouts.app :title="__('app.processes')">
    <x-slot:header>{{ __('app.processes') }}</x-slot:header>

    <div class="sm:flex sm:items-center sm:justify-between">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search') }}..."
                   class="rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
            <select name="status" class="rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                <option value="">{{ __('app.status') }}</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>{{ __('app.process_status_draft') }}</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.process_status_active') }}</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>{{ __('app.process_status_archived') }}</option>
            </select>
            <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('app.search') }}</button>
        </form>
        @if(auth()->user()->canManageProcesses())
            <a href="{{ route('processes.create') }}" class="mt-3 sm:mt-0 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                {{ __('app.create_process') }}
            </a>
        @endif
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($processes as $process)
            <div class="overflow-hidden rounded-lg bg-white shadow hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                            {{ $process->status === 'active' ? 'bg-green-100 text-green-800' :
                               ($process->status === 'draft' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ __('app.process_status_' . $process->status) }}
                        </span>
                        <span class="text-xs text-gray-400">v{{ $process->version }}</span>
                    </div>
                    <h3 class="mt-3 text-lg font-semibold text-gray-900">{{ $process->localizedName() }}</h3>
                    @if($process->localizedDescription())
                        <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $process->localizedDescription() }}</p>
                    @endif
                    <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                        <span>{{ $process->steps_count }} {{ __('app.steps') }}</span>
                        <span>{{ $process->runs_count }} {{ __('app.runs') }}</span>
                        @if($process->category)
                            <span class="rounded bg-gray-100 px-2 py-0.5">{{ $process->category }}</span>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-400">{{ __('app.created_by') }}: {{ $process->creator->name }}</p>
                </div>
                <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 flex items-center justify-between">
                    <a href="{{ route('processes.show', $process) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ __('app.preview') }}</a>
                    <div class="flex items-center gap-2">
                        @if($process->isActive())
                            <form method="POST" action="{{ route('runs.start', $process) }}">
                                @csrf
                                <button type="submit" class="rounded bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-indigo-500">{{ __('app.start_run') }}</button>
                            </form>
                        @endif
                        @if(auth()->user()->canManageProcesses() && (auth()->user()->isAdmin() || $process->created_by === auth()->id()))
                            <a href="{{ route('processes.edit', $process) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('app.edit') }}</a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-gray-500">{{ __('app.no_results') }}</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $processes->links() }}</div>
</x-layouts.app>
