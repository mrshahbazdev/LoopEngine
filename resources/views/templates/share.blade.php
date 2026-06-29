<x-layouts.app :title="__('app.share_template')">
    <x-slot:header>{{ __('app.share_template') }}</x-slot:header>

    <div class="mx-auto max-w-2xl">
        <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow transition-colors animate-scale-in">
            <form method="POST" action="{{ route('templates.store-share') }}" class="p-6 space-y-5">
                @csrf

                <div>
                    <label for="process_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.select_process') }}</label>
                    <select name="process_id" id="process_id" required class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                        <option value="">-- {{ __('app.choose') }} --</option>
                        @foreach($processes as $process)
                            <option value="{{ $process->id }}">{{ $process->localizedName() }}</option>
                        @endforeach
                    </select>
                    @error('process_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="name_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.name_en') }}</label>
                        <input type="text" name="name_en" id="name_en" required value="{{ old('name_en') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                        @error('name_en') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="name_de" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.name_de') }}</label>
                        <input type="text" name="name_de" id="name_de" value="{{ old('name_de') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                    </div>
                </div>

                <div>
                    <label for="description_en" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.description_en') }}</label>
                    <textarea name="description_en" id="description_en" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">{{ old('description_en') }}</textarea>
                </div>

                <div>
                    <label for="description_de" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.description_de') }}</label>
                    <textarea name="description_de" id="description_de" rows="3" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">{{ old('description_de') }}</textarea>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.category') }}</label>
                        <input type="text" name="category" id="category" value="{{ old('category') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                    </div>
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.tags') }}</label>
                        <input type="text" name="tags" id="tags" value="{{ old('tags') }}" placeholder="{{ __('app.tags_placeholder') }}" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm transition-colors">
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-5">
                    <a href="{{ route('templates.index') }}" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        {{ __('app.cancel') }}
                    </a>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                        {{ __('app.share') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
