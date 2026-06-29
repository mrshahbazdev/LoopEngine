<x-layouts.app :title="__('app.templates')">
    <x-slot:header>{{ __('app.template_marketplace') }}</x-slot:header>

    {{-- Search & Filter Bar --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between animate-fade-in">
        <form method="GET" class="flex flex-1 gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search_templates') }}"
                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
            <select name="category" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                <option value="">{{ __('app.all_categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <select name="sort" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                <option value="newest" {{ $sortBy === 'newest' ? 'selected' : '' }}>{{ __('app.newest') }}</option>
                <option value="popular" {{ $sortBy === 'popular' ? 'selected' : '' }}>{{ __('app.most_popular') }}</option>
                <option value="rating" {{ $sortBy === 'rating' ? 'selected' : '' }}>{{ __('app.highest_rated') }}</option>
            </select>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                {{ __('app.filter') }}
            </button>
        </form>
        @if(auth()->user()->canManageProcesses())
            <a href="{{ route('templates.share') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500 btn-press transition-colors">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z" />
                </svg>
                {{ __('app.share_template') }}
            </a>
        @endif
    </div>

    {{-- Template Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($templates as $template)
            <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow card-hover transition-colors animate-fade-in stagger-{{ min($loop->iteration, 6) }}">
                <div class="p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $template->localizedName() }}</h3>
                            @if($template->category)
                                <span class="mt-1 inline-block rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-400">{{ $template->category }}</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1 text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-4 w-4 {{ $i <= round($template->rating) ? 'fill-current' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z" clip-rule="evenodd" />
                                </svg>
                            @endfor
                            <span class="ml-1 text-xs text-gray-500 dark:text-gray-400">({{ $template->rating_count }})</span>
                        </div>
                    </div>
                    @if($template->localizedDescription())
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">{{ $template->localizedDescription() }}</p>
                    @endif
                    @if($template->tags)
                        <div class="mt-3 flex flex-wrap gap-1">
                            @foreach($template->tags as $tag)
                                <span class="rounded bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-600 dark:text-gray-400">{{ $tag }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ __('app.by') }} {{ $template->sharedBy->name }}</span>
                        <span>{{ $template->install_count }} {{ __('app.installs') }}</span>
                    </div>
                </div>
                <div class="border-t border-gray-200 dark:border-gray-700 px-5 py-3 flex items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                    <a href="{{ route('templates.show', $template) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors">
                        {{ __('app.view_details') }}
                    </a>
                    <form method="POST" action="{{ route('templates.install', $template) }}">
                        @csrf
                        <button type="submit" class="rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                            {{ __('app.install') }}
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375" />
                </svg>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('app.no_templates') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('app.no_templates_desc') }}</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $templates->links() }}</div>
</x-layouts.app>
