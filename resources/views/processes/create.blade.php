<x-layouts.app :title="__('app.create_process')">
    <x-slot:header>{{ __('app.create_process') }}</x-slot:header>

    <div class="mx-auto max-w-2xl">
        <form method="POST" action="{{ route('processes.store') }}" class="space-y-6 bg-white shadow sm:rounded-lg p-6">
            @csrf
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name_en" class="block text-sm font-medium text-gray-700">{{ __('app.process_name_en') }} *</label>
                    <input type="text" name="name_en" id="name_en" required value="{{ old('name_en') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name_en') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="name_de" class="block text-sm font-medium text-gray-700">{{ __('app.process_name_de') }}</label>
                    <input type="text" name="name_de" id="name_de" value="{{ old('name_de') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="description_en" class="block text-sm font-medium text-gray-700">{{ __('app.description_en') }}</label>
                    <textarea name="description_en" id="description_en" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description_en') }}</textarea>
                </div>
                <div>
                    <label for="description_de" class="block text-sm font-medium text-gray-700">{{ __('app.description_de') }}</label>
                    <textarea name="description_de" id="description_de" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description_de') }}</textarea>
                </div>
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">{{ __('app.category') }}</label>
                <input type="text" name="category" id="category" value="{{ old('category') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('processes.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">{{ __('app.cancel') }}</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">{{ __('app.create') }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
