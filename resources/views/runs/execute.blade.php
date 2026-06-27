<x-layouts.app :title="__('app.run')">
    <x-slot:header>{{ $run->process->localizedName() }}</x-slot:header>

    <div class="mx-auto max-w-2xl">
        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                <span>{{ __('app.step') }} {{ $progress['current'] }} / {{ $progress['total'] }}</span>
                <span>{{ __('app.loop_count') }}: {{ $run->loop_count }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $progress['percentage'] }}%"></div>
            </div>
        </div>

        {{-- Step Card --}}
        <div class="bg-white shadow-lg sm:rounded-xl overflow-hidden">
            {{-- Step Header --}}
            <div class="px-6 py-4 {{ $step->is_loop_checkpoint ? 'bg-indigo-600' : 'bg-gray-900' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20 text-sm font-bold text-white">
                            {{ $step->order + 1 }}
                        </span>
                        <span class="text-sm font-medium text-white/80">
                            {{ __('app.step_type_' . $step->step_type) }}
                            @if($step->is_loop_checkpoint)
                                &middot; &#x21bb; {{ __('app.is_loop_checkpoint') }}
                            @endif
                        </span>
                    </div>
                    @if($step->max_loops > 0)
                        <span class="text-xs text-white/60">{{ __('app.max_loops') }}: {{ $step->max_loops }}</span>
                    @endif
                </div>
            </div>

            {{-- Question --}}
            <div class="px-6 py-8">
                <h2 class="text-xl font-semibold text-gray-900">{{ $step->localizedQuestion() }}</h2>
                @if($step->localizedHelpText())
                    <p class="mt-2 text-sm text-gray-500">{{ $step->localizedHelpText() }}</p>
                @endif

                {{-- Answer Form --}}
                <form method="POST" action="{{ route('runs.answer', $run) }}" class="mt-8">
                    @csrf

                    @if($step->options->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($step->options as $option)
                                <label class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none hover:border-indigo-400 transition-colors"
                                       x-data="{ selected: false }">
                                    <input type="radio" name="option_id" value="{{ $option->id }}" class="sr-only" required
                                           @change="document.querySelectorAll('[data-option]').forEach(el => el.classList.remove('border-indigo-600', 'bg-indigo-50')); $el.closest('label').classList.add('border-indigo-600', 'bg-indigo-50')">
                                    <span class="flex flex-1 items-center" data-option>
                                        <span class="flex flex-col">
                                            <span class="flex items-center gap-2">
                                                @if($option->color)
                                                    <span class="h-3 w-3 rounded-full
                                                        {{ $option->color === 'green' ? 'bg-green-400' :
                                                           ($option->color === 'red' ? 'bg-red-400' :
                                                           ($option->color === 'yellow' ? 'bg-yellow-400' : 'bg-blue-400')) }}"></span>
                                                @endif
                                                <span class="text-sm font-medium text-gray-900">{{ $option->localizedLabel() }}</span>
                                            </span>
                                        </span>
                                    </span>
                                    <svg class="h-5 w-5 text-indigo-600 hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div>
                            <textarea name="response_text" rows="4" required placeholder="{{ __('app.answer_question') }}"
                                      class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>
                    @endif

                    <div class="mt-6 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('runs.pause', $run) }}">
                                @csrf
                                <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('app.pause_run') }}</button>
                            </form>
                            <form method="POST" action="{{ route('runs.cancel', $run) }}">
                                @csrf
                                <button type="submit" onclick="return confirm('Cancel this run?')" class="text-sm text-red-600 hover:text-red-800 ml-2">{{ __('app.cancel_run') }}</button>
                            </form>
                        </div>
                        <button type="submit" class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            {{ __('app.submit_answer') }} &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
