<x-layouts.app :title="$template->localizedName()">
    <x-slot:header>{{ $template->localizedName() }}</x-slot:header>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Details --}}
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-fade-in">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            @if($template->category)
                                <span class="inline-block rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-3 py-1 text-sm font-medium text-indigo-800 dark:text-indigo-400">{{ $template->category }}</span>
                            @endif
                            @if($template->localizedDescription())
                                <p class="mt-3 text-gray-600 dark:text-gray-400">{{ $template->localizedDescription() }}</p>
                            @endif
                        </div>
                    </div>

                    @if($template->tags)
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach($template->tags as $tag)
                                <span class="rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-sm text-gray-600 dark:text-gray-400">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-6 grid grid-cols-3 gap-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $template->install_count }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.installs') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $template->process->steps->count() }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.steps') }}</div>
                        </div>
                        <div class="text-center">
                            <div class="flex items-center justify-center gap-1 text-2xl font-bold text-yellow-500">{{ number_format($template->rating, 1) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('app.rating') }} ({{ $template->rating_count }})</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Process Steps Preview --}}
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-slide-in-up stagger-1">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('app.process_steps') }}</h3>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($template->process->steps as $step)
                        <li class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $step->is_loop_checkpoint ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-400' }} text-sm font-semibold">
                                    {{ $step->order }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $step->localizedQuestion() }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $step->step_type }}</span>
                                        @if($step->is_loop_checkpoint)
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:text-yellow-400">Loop</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Reviews --}}
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-slide-in-up stagger-2">
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('app.reviews') }}</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($template->ratings as $r)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $r->user->name }}</span>
                                <div class="flex items-center gap-0.5 text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="h-3.5 w-3.5 {{ $i <= $r->rating ? 'fill-current' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            @if($r->review)
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $r->review }}</p>
                            @endif
                            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $r->created_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_reviews') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Actions --}}
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-slide-in-up stagger-3 p-6">
                <form method="POST" action="{{ route('templates.install', $template) }}" class="mb-4">
                    @csrf
                    <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                        {{ __('app.install_template') }}
                    </button>
                </form>
                <a href="{{ route('templates.export', $template) }}" class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2.5 text-center text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    {{ __('app.export_json') }}
                </a>
                <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                    <p>{{ __('app.shared_by') }}: {{ $template->sharedBy->name }}</p>
                    <p class="mt-1">{{ __('app.shared_on') }}: {{ $template->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            {{-- Rate --}}
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-slide-in-up stagger-4 p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">{{ __('app.rate_template') }}</h3>
                <form method="POST" action="{{ route('templates.rate', $template) }}">
                    @csrf
                    <div x-data="{ rating: {{ $userRating?->rating ?? 0 }} }" class="flex items-center gap-1 mb-3">
                        @for($i = 1; $i <= 5; $i++)
                            <button type="button" @click="rating = {{ $i }}" class="focus:outline-none">
                                <svg class="h-6 w-6 cursor-pointer transition-colors" :class="rating >= {{ $i }} ? 'text-yellow-400 fill-current' : 'text-gray-300 dark:text-gray-600'" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        @endfor
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <textarea name="review" rows="3" placeholder="{{ __('app.write_review') }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">{{ $userRating?->review }}</textarea>
                    <button type="submit" class="mt-3 w-full rounded-lg bg-yellow-500 px-4 py-2 text-sm font-semibold text-white hover:bg-yellow-400 btn-press transition-colors">
                        {{ __('app.submit_rating') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
