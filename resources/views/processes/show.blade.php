<x-layouts.app :title="$process->localizedName()">
    <x-slot:header>{{ $process->localizedName() }}</x-slot:header>

    <div class="space-y-6">
        {{-- Process Info --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 transition-colors animate-fade-in">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $process->localizedName() }}</h2>
                    @if($process->localizedDescription())
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $process->localizedDescription() }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium
                        {{ $process->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                           ($process->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300') }}">
                        {{ __('app.process_status_' . $process->status) }}
                    </span>
                    @if($process->isActive())
                        <form method="POST" action="{{ route('runs.start', $process) }}">
                            @csrf
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 btn-press transition-colors">{{ __('app.start_run') }}</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-6 text-sm text-gray-500 dark:text-gray-400">
                <span>{{ __('app.version') }}: {{ $process->version }}</span>
                @if($process->category)
                    <span>{{ __('app.category') }}: {{ $process->category }}</span>
                @endif
                <span>{{ __('app.created_by') }}: {{ $process->creator->name }}</span>
                <span>{{ $process->steps->count() }} {{ __('app.steps') }}</span>
            </div>
        </div>

        {{-- Process Flow --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 transition-colors animate-slide-in-up">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">{{ __('app.process_flow') }}</h3>
            <div class="space-y-4">
                @foreach($process->steps as $step)
                    <div class="flex gap-4 animate-fade-in stagger-{{ min($loop->iteration, 6) }}">
                        <div class="flex flex-col items-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full
                                {{ $step->is_loop_checkpoint ? 'bg-indigo-600 text-white' :
                                   ($step->step_type === 'end' ? 'bg-red-600 text-white' :
                                   ($step->step_type === 'decision' ? 'bg-yellow-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200')) }}
                                text-sm font-bold">
                                {{ $step->order + 1 }}
                            </div>
                            @if(!$loop->last)
                                <div class="w-0.5 flex-1 bg-gray-200 dark:bg-gray-600 my-1"></div>
                            @endif
                        </div>
                        <div class="flex-1 pb-4">
                            <div class="rounded-lg border {{ $step->is_loop_checkpoint ? 'border-indigo-200 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700' }} p-4 transition-colors">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-medium rounded-full px-2 py-0.5
                                        {{ $step->step_type === 'loop_check' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' :
                                           ($step->step_type === 'decision' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                           ($step->step_type === 'end' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300')) }}">
                                        {{ __('app.step_type_' . $step->step_type) }}
                                    </span>
                                    @if($step->is_loop_checkpoint)
                                        <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">&#x21bb; {{ __('app.is_loop_checkpoint') }}</span>
                                    @endif
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $step->localizedQuestion() }}</p>
                                @if($step->localizedHelpText())
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $step->localizedHelpText() }}</p>
                                @endif
                                @if($step->options->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($step->options as $option)
                                            <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset
                                                {{ $option->color === 'green' ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/20 dark:text-green-400 dark:ring-green-400/20' :
                                                   ($option->color === 'red' ? 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-900/20 dark:text-red-400 dark:ring-red-400/20' :
                                                   ($option->color === 'yellow' ? 'bg-yellow-50 text-yellow-700 ring-yellow-600/20 dark:bg-yellow-900/20 dark:text-yellow-400 dark:ring-yellow-400/20' :
                                                   ($option->color === 'blue' ? 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-400/20' : 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-500/20'))) }}">
                                                {{ $option->localizedLabel() }}
                                                @if($option->transition)
                                                    <span class="ml-1 text-gray-400 dark:text-gray-500">&rarr; {{ __('app.action_' . $option->transition->action_type) }}</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
