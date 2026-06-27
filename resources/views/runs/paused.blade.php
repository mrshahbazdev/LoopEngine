<x-layouts.app :title="__('app.run_status_paused')">
    <x-slot:header>{{ $run->process->localizedName() }}</x-slot:header>

    <div class="mx-auto max-w-md text-center py-16">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-yellow-100">
            <svg class="h-8 w-8 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5" />
            </svg>
        </div>
        <h2 class="mt-4 text-lg font-semibold text-gray-900">{{ __('app.run_status_paused') }}</h2>
        <p class="mt-2 text-sm text-gray-600">{{ $run->process->localizedName() }}</p>
        <div class="mt-6 flex items-center justify-center gap-3">
            <form method="POST" action="{{ route('runs.resume', $run) }}">
                @csrf
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('app.resume_run') }}</button>
            </form>
            <form method="POST" action="{{ route('runs.cancel', $run) }}">
                @csrf
                <button type="submit" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('app.cancel_run') }}</button>
            </form>
        </div>
    </div>
</x-layouts.app>
