<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.register') }} - {{ __('app.app_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
    <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <svg class="h-10 w-10 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 14.652" />
                    </svg>
                    <span class="text-2xl font-bold text-gray-900">{{ __('app.app_name') }}</span>
                </a>
            </div>
            <h2 class="mt-6 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">{{ __('app.register') }}</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-[480px]">
            <div class="bg-white px-6 py-12 shadow sm:rounded-lg sm:px-12">
                <form class="space-y-6" method="POST" action="{{ route('register') }}">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900">{{ __('app.name') }}</label>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900">{{ __('app.email') }}</label>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                               class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900">{{ __('app.password') }}</label>
                        <input id="password" name="password" type="password" required
                               class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium leading-6 text-gray-900">{{ __('app.password_confirm') }}</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                               class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        {{ __('app.register') }}
                    </button>
                </form>
            </div>

            <p class="mt-10 text-center text-sm text-gray-500">
                {{ __('app.already_registered') }}
                <a href="{{ route('login') }}" class="font-semibold leading-6 text-indigo-600 hover:text-indigo-500">{{ __('app.login') }}</a>
            </p>
        </div>
    </div>
</body>
</html>
