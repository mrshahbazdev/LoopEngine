<x-layouts.app :title="__('app.assign_process')">
    <x-slot:header>{{ __('app.assign_process') }}</x-slot:header>

    <div class="mx-auto max-w-lg animate-slide-in-up">
        <form method="POST" action="{{ route('team.assign') }}" class="space-y-6 rounded-lg bg-white dark:bg-gray-800 p-6 shadow transition-colors">
            @csrf

            <div>
                <label for="process_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.process') }} *</label>
                <select name="process_id" id="process_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">{{ __('app.select_process') }}</option>
                    @foreach($processes as $process)
                        <option value="{{ $process->id }}" {{ old('process_id') == $process->id ? 'selected' : '' }}>
                            {{ $process->localizedName() }}
                        </option>
                    @endforeach
                </select>
                @error('process_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.assigned_to') }} *</label>
                <select name="user_id" id="user_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">{{ __('app.select_user') }}</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ __('app.role_' . $user->role) }})
                        </option>
                    @endforeach
                </select>
                @error('user_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('app.notes') }}</label>
                <textarea name="notes" id="notes" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('team.assignments') }}" class="rounded-md bg-white dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 btn-press transition-colors">
                    {{ __('app.cancel') }}
                </a>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">
                    {{ __('app.assign_process') }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
