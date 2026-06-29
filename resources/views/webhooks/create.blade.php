<x-layouts.app :title="__('app.create_webhook')">
    <x-slot:header>{{ __('app.create_webhook') }}</x-slot:header>

    <div class="mx-auto max-w-2xl">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-scale-in">
            <form method="POST" action="{{ route('webhooks.store') }}" class="p-6 space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.name') }}</label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                    @error('name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.webhook_url') }}</label>
                    <input type="url" name="url" id="url" required value="{{ old('url') }}" placeholder="https://example.com/webhook" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                    @error('url') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="secret" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.webhook_secret') }}</label>
                    <input type="text" name="secret" id="secret" value="{{ old('secret') }}" placeholder="{{ __('app.optional') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('app.webhook_secret_help') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('app.events') }}</label>
                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        @foreach($availableEvents as $value => $label)
                            <label class="flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-600 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors">
                                <input type="checkbox" name="events[]" value="{{ $value }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700" {{ in_array($value, old('events', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('events') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-5">
                    <a href="{{ route('webhooks.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        {{ __('app.cancel') }}
                    </a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                        {{ __('app.create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
