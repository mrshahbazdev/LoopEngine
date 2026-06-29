<x-layouts.app :title="__('app.edit_process')">
    <x-slot:header>{{ __('app.edit_process') }}: {{ $process->localizedName() }}</x-slot:header>

    <div class="space-y-8">
        {{-- Process Details --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 transition-colors animate-fade-in">
            <div class="flex flex-wrap items-center justify-between mb-4 gap-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('app.process') }}</h3>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                        {{ $process->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' :
                           ($process->status === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300') }}">
                        {{ __('app.process_status_' . $process->status) }}
                    </span>
                    @if($process->status === 'draft')
                        <form method="POST" action="{{ route('processes.activate', $process) }}">
                            @csrf
                            <button type="submit" class="rounded bg-green-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-green-500 btn-press transition-colors">{{ __('app.activate') }}</button>
                        </form>
                    @elseif($process->status === 'active')
                        <form method="POST" action="{{ route('processes.archive', $process) }}">
                            @csrf
                            <button type="submit" class="rounded bg-gray-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-gray-500 btn-press transition-colors">{{ __('app.archive') }}</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('processes.duplicate', $process) }}">
                        @csrf
                        <button type="submit" class="rounded bg-blue-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-blue-500 btn-press transition-colors">{{ __('app.duplicate') }}</button>
                    </form>
                    <form method="POST" action="{{ route('processes.version', $process) }}">
                        @csrf
                        <button type="submit" class="rounded bg-purple-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-purple-500 btn-press transition-colors">{{ __('app.new_version') }}</button>
                    </form>
                </div>
            </div>
            <form method="POST" action="{{ route('processes.update', $process) }}" class="space-y-4">
                @csrf @method('PUT')
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.process_name_en') }} *</label>
                        <input type="text" name="name_en" required value="{{ old('name_en', $process->name_en) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.process_name_de') }}</label>
                        <input type="text" name="name_de" value="{{ old('name_de', $process->name_de) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.description_en') }}</label>
                        <textarea name="description_en" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description_en', $process->description_en) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.description_de') }}</label>
                        <textarea name="description_de" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description_de', $process->description_de) }}</textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.category') }}</label>
                    <input type="text" name="category" value="{{ old('category', $process->category) }}"
                           class="mt-1 block w-full max-w-xs rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 btn-press transition-colors">{{ __('app.save') }}</button>
            </form>
        </div>

        {{-- Steps --}}
        <div class="bg-white dark:bg-gray-800 shadow sm:rounded-lg p-6 transition-colors animate-slide-in-up">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('app.steps') }}</h3>
            </div>

            {{-- Existing Steps --}}
            <div class="space-y-4 mb-8" id="steps-sortable" x-data="stepReorder({{ $process->id }})">
                @forelse($process->steps as $step)
                    <div class="rounded-lg border {{ $step->is_loop_checkpoint ? 'border-indigo-300 dark:border-indigo-700 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700' }} p-4 transition-colors" x-data="{ expanded: false }" data-step-id="{{ $step->id }}">
                        <div class="flex items-center justify-between cursor-pointer" @click="expanded = !expanded">
                            <div class="flex items-center gap-3">
                                <span class="drag-handle cursor-grab active:cursor-grabbing flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-200 dark:hover:bg-indigo-800/50 transition-colors" @click.stop title="{{ __('app.drag_to_reorder') }}">{{ $step->order + 1 }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $step->question_en }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ __('app.step_type_' . $step->step_type) }}</span>
                                        @if($step->is_loop_checkpoint)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">{{ __('app.is_loop_checkpoint') }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <svg class="h-5 w-5 text-gray-400 dark:text-gray-500 transition-transform" :class="expanded && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                        </div>

                        <div x-show="expanded" x-cloak class="mt-4 space-y-4 border-t dark:border-gray-700 pt-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                            {{-- Edit Step --}}
                            <form method="POST" action="{{ route('steps.update', [$process, $step]) }}" class="space-y-3">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.question_en') }}</label>
                                        <textarea name="question_en" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ $step->question_en }}</textarea>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.question_de') }}</label>
                                        <textarea name="question_de" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ $step->question_de }}</textarea>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.help_text_en') }}</label>
                                        <input type="text" name="help_text_en" value="{{ $step->help_text_en }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.help_text_de') }}</label>
                                        <input type="text" name="help_text_de" value="{{ $step->help_text_de }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.step_type') }}</label>
                                        <select name="step_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                            @foreach(['question','decision','loop_check','info','end'] as $type)
                                                <option value="{{ $type }}" {{ $step->step_type === $type ? 'selected' : '' }}>{{ __('app.step_type_' . $type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.max_loops') }}</label>
                                        <input type="number" name="max_loops" min="0" value="{{ $step->max_loops }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    </div>
                                    <div class="flex items-end gap-4">
                                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <input type="hidden" name="is_loop_checkpoint" value="0">
                                            <input type="checkbox" name="is_loop_checkpoint" value="1" {{ $step->is_loop_checkpoint ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                                            {{ __('app.is_loop_checkpoint') }}
                                        </label>
                                    </div>
                                    <div class="flex items-end gap-4">
                                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <input type="hidden" name="is_required" value="0">
                                            <input type="checkbox" name="is_required" value="1" {{ $step->is_required ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                                            {{ __('app.is_required') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="rounded bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">{{ __('app.save') }}</button>
                                </div>
                            </form>

                            {{-- Options --}}
                            <div class="border-t dark:border-gray-700 pt-3">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.options') }}</h4>
                                @foreach($step->options as $option)
                                    <div class="flex items-center justify-between py-1">
                                        <div class="flex items-center gap-2">
                                            @if($option->color)
                                                <span class="h-3 w-3 rounded-full bg-{{ $option->color }}-400"></span>
                                            @endif
                                            <span class="text-sm text-gray-900 dark:text-gray-200">{{ $option->label_en }}</span>
                                            @if($option->label_de)
                                                <span class="text-xs text-gray-400 dark:text-gray-500">({{ $option->label_de }})</span>
                                            @endif
                                            @if($option->transition)
                                                <span class="text-xs text-indigo-600 dark:text-indigo-400">&rarr; {{ __('app.action_' . $option->transition->action_type) }}</span>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('options.destroy', $option) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">{{ __('app.delete') }}</button>
                                        </form>
                                    </div>
                                @endforeach

                                {{-- Add Option --}}
                                <form method="POST" action="{{ route('options.store', $step) }}" class="mt-2 flex flex-wrap items-end gap-2">
                                    @csrf
                                    <input type="text" name="label_en" placeholder="{{ __('app.option_label_en') }}" required class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-36">
                                    <input type="text" name="label_de" placeholder="{{ __('app.option_label_de') }}" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-36">
                                    <input type="text" name="value" placeholder="{{ __('app.option_value') }}" required class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-24">
                                    <select name="color" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-28">
                                        <option value="">{{ __('app.option_color') }}</option>
                                        <option value="green">{{ __('app.color_green') }}</option>
                                        <option value="red">{{ __('app.color_red') }}</option>
                                        <option value="yellow">{{ __('app.color_yellow') }}</option>
                                        <option value="blue">{{ __('app.color_blue') }}</option>
                                    </select>
                                    <button type="submit" class="rounded bg-gray-800 dark:bg-gray-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-gray-700 dark:hover:bg-gray-500 btn-press transition-colors">{{ __('app.add_option') }}</button>
                                </form>
                            </div>

                            {{-- Transitions --}}
                            <div class="border-t dark:border-gray-700 pt-3">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.transitions') }}</h4>
                                @foreach($step->transitions as $transition)
                                    <div class="flex items-center justify-between py-1">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">
                                            @if($transition->option)
                                                {{ $transition->option->label_en }} &rarr;
                                            @else
                                                {{ __('app.default_transition') }} &rarr;
                                            @endif
                                            {{ __('app.action_' . $transition->action_type) }}
                                            @if($transition->targetStep)
                                                ({{ __('app.step') }} {{ $transition->targetStep->order + 1 }})
                                            @endif
                                        </span>
                                        <form method="POST" action="{{ route('transitions.destroy', $transition) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">{{ __('app.delete') }}</button>
                                        </form>
                                    </div>
                                @endforeach

                                {{-- Add Transition --}}
                                <form method="POST" action="{{ route('transitions.store', $step) }}" class="mt-2 flex flex-wrap items-end gap-2">
                                    @csrf
                                    <select name="option_id" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-36">
                                        <option value="">{{ __('app.default_transition') }}</option>
                                        @foreach($step->options as $opt)
                                            <option value="{{ $opt->id }}">{{ $opt->label_en }}</option>
                                        @endforeach
                                    </select>
                                    <select name="action_type" required class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-40">
                                        <option value="next_step">{{ __('app.action_next_step') }}</option>
                                        <option value="goto_step">{{ __('app.action_goto_step') }}</option>
                                        <option value="loop_back">{{ __('app.action_loop_back') }}</option>
                                        <option value="start_process">{{ __('app.action_start_process') }}</option>
                                        <option value="end">{{ __('app.action_end') }}</option>
                                    </select>
                                    <select name="target_step_id" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-40">
                                        <option value="">{{ __('app.target_step') }}</option>
                                        @foreach($process->steps as $s)
                                            <option value="{{ $s->id }}">{{ $s->order + 1 }}. {{ Str::limit($s->question_en, 30) }}</option>
                                        @endforeach
                                    </select>
                                    <select name="target_process_id" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-40">
                                        <option value="">{{ __('app.target_process') }}</option>
                                        @foreach($allProcesses as $p)
                                            <option value="{{ $p->id }}">{{ $p->name_en }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="rounded bg-gray-800 dark:bg-gray-600 px-2 py-1.5 text-xs font-semibold text-white hover:bg-gray-700 dark:hover:bg-gray-500 btn-press transition-colors">{{ __('app.add_transition') }}</button>
                                </form>
                            </div>

                            {{-- Delete Step --}}
                            <div class="border-t dark:border-gray-700 pt-3 flex justify-end">
                                <form method="POST" action="{{ route('steps.destroy', [$process, $step]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Delete this step?')" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 transition-colors">{{ __('app.delete') }} {{ __('app.step') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">{{ __('app.no_steps') }}</p>
                @endforelse
            </div>

            {{-- Add Step --}}
            <div class="rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-4 transition-colors">
                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('app.add_step') }}</h4>
                <form method="POST" action="{{ route('steps.store', $process) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.question_en') }} *</label>
                            <textarea name="question_en" rows="2" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="{{ __('app.question_en') }}"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.question_de') }}</label>
                            <textarea name="question_de" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="{{ __('app.question_de') }}"></textarea>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.step_type') }}</label>
                            <select name="step_type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                @foreach(['question','decision','loop_check','info','end'] as $type)
                                    <option value="{{ $type }}">{{ __('app.step_type_' . $type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">{{ __('app.max_loops') }}</label>
                            <input type="number" name="max_loops" min="0" value="0" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="hidden" name="is_loop_checkpoint" value="0">
                                <input type="checkbox" name="is_loop_checkpoint" value="1" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                                {{ __('app.is_loop_checkpoint') }}
                            </label>
                        </div>
                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <input type="hidden" name="is_required" value="0">
                                <input type="checkbox" name="is_required" value="1" checked class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                                {{ __('app.is_required') }}
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 btn-press transition-colors">{{ __('app.add_step') }}</button>
                </form>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('processes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">&larr; {{ __('app.back') }}</a>
            <form method="POST" action="{{ route('processes.destroy', $process) }}">
                @csrf @method('DELETE')
                <button type="submit" onclick="return confirm('{{ __('app.confirm_delete_process') }}')"
                        class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 btn-press transition-colors">{{ __('app.delete') }}</button>
            </form>
        </div>
    </div>
</x-layouts.app>
