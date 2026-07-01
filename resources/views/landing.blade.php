<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.app_name') }} - {{ __('app.tagline') }}</title>
    <script>
        (function() {
            var s = localStorage.getItem('easysop-theme');
            if (s === 'dark' || (!s && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-white dark:bg-gray-900 transition-colors duration-300" x-data="darkMode()">
    {{-- Nav --}}
    <nav class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 transition-colors">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 14.652" />
                    </svg>
                    <span class="text-xl font-bold text-gray-900 dark:text-white">{{ __('app.app_name') }}</span>
                </div>
                <div class="flex items-center gap-4">
                    {{-- Dark mode toggle --}}
                    <button @click="toggle()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 transition-colors btn-press">
                        <svg x-show="!dark" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                        <svg x-show="dark" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                    </button>
                    <div class="flex items-center gap-2 text-sm">
                        <a href="{{ route('locale.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'font-bold text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">EN</a>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <a href="{{ route('locale.switch', 'de') }}" class="{{ app()->getLocale() === 'de' ? 'font-bold text-indigo-600 dark:text-indigo-400' : 'text-gray-500 dark:text-gray-400' }}">DE</a>
                    </div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">{{ __('app.dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">{{ __('app.login') }}</a>
                        <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 btn-press transition-colors">{{ __('app.register') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <div class="relative isolate overflow-hidden bg-gradient-to-b from-indigo-100/20 dark:from-indigo-900/20">
        <div class="mx-auto max-w-7xl px-6 pb-24 pt-16 sm:pb-32 lg:flex lg:px-8 lg:py-40">
            <div class="mx-auto max-w-2xl lg:mx-0 lg:max-w-xl lg:flex-shrink-0 lg:pt-8 animate-fade-in">
                <div class="mt-24 sm:mt-32 lg:mt-0">
                    <span class="inline-flex items-center space-x-2 rounded-full bg-indigo-600/10 dark:bg-indigo-400/10 px-3 py-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 ring-1 ring-inset ring-indigo-600/20 dark:ring-indigo-400/20">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 14.652" />
                        </svg>
                        <span>{{ __('app.app_name') }}</span>
                    </span>
                </div>
                <h1 class="mt-10 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">{{ __('app.hero_title') }}</h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">{{ __('app.hero_subtitle') }}</p>
                <div class="mt-10 flex items-center gap-x-6">
                    <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 btn-press transition-colors">{{ __('app.get_started') }}</a>
                    <a href="#how-it-works" class="text-sm font-semibold leading-6 text-gray-900 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ __('app.learn_more') }} <span aria-hidden="true">&rarr;</span></a>
                </div>
            </div>
            <div class="mx-auto mt-16 flex max-w-2xl sm:mt-24 lg:ml-10 lg:mr-0 lg:mt-0 lg:max-w-none lg:flex-none xl:ml-32">
                <div class="max-w-3xl flex-none sm:max-w-5xl lg:max-w-none animate-slide-in-up stagger-2">
                    {{-- Visual loop diagram --}}
                    <div class="relative rounded-xl bg-indigo-950 p-8 shadow-2xl ring-1 ring-white/10 sm:p-12" style="width: 400px;">
                        <div class="text-center">
                            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-indigo-600 animate-pulse-subtle">
                                <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 14.652" />
                                </svg>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3 rounded-lg bg-white/5 px-4 py-3 animate-slide-in-left stagger-1">
                                    <div class="h-3 w-3 rounded-full bg-green-400"></div>
                                    <span class="text-sm text-indigo-200">{{ __('app.step1_title') }}</span>
                                </div>
                                <svg class="mx-auto h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" /></svg>
                                <div class="flex items-center gap-3 rounded-lg bg-white/5 px-4 py-3 animate-slide-in-left stagger-2">
                                    <div class="h-3 w-3 rounded-full bg-yellow-400"></div>
                                    <span class="text-sm text-indigo-200">{{ __('app.step2_title') }}</span>
                                </div>
                                <svg class="mx-auto h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" /></svg>
                                <div class="flex items-center gap-3 rounded-lg bg-white/10 px-4 py-3 ring-1 ring-indigo-400/30 animate-slide-in-left stagger-3">
                                    <div class="h-3 w-3 rounded-full bg-indigo-400"></div>
                                    <span class="text-sm font-medium text-white">{{ __('app.step3_title') }}</span>
                                </div>
                                <svg class="mx-auto h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" /></svg>
                                <div class="flex items-center gap-3 rounded-lg bg-white/5 px-4 py-3 animate-slide-in-left stagger-4">
                                    <div class="h-3 w-3 rounded-full bg-emerald-400"></div>
                                    <span class="text-sm text-indigo-200">{{ __('app.step4_title') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Features --}}
    <div class="mx-auto max-w-7xl px-6 lg:px-8 py-24">
        <div class="mx-auto max-w-2xl lg:text-center animate-fade-in">
            <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">{{ __('app.app_name') }}</h2>
            <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">{{ __('app.tagline') }}</p>
            <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">{{ __('app.subtitle') }}</p>
        </div>
        <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
            <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                <div class="relative pl-16 animate-fade-in stagger-1 card-hover rounded-lg p-4">
                    <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 ml-4 mt-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182M2.985 14.652" /></svg>
                        </div>
                        {{ __('app.feature_1_title') }}
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('app.feature_1_desc') }}</dd>
                </div>
                <div class="relative pl-16 animate-fade-in stagger-2 card-hover rounded-lg p-4">
                    <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 ml-4 mt-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        {{ __('app.feature_2_title') }}
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('app.feature_2_desc') }}</dd>
                </div>
                <div class="relative pl-16 animate-fade-in stagger-3 card-hover rounded-lg p-4">
                    <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 ml-4 mt-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        </div>
                        {{ __('app.feature_3_title') }}
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('app.feature_3_desc') }}</dd>
                </div>
                <div class="relative pl-16 animate-fade-in stagger-4 card-hover rounded-lg p-4">
                    <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                        <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600 ml-4 mt-4">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" /></svg>
                        </div>
                        {{ __('app.feature_4_title') }}
                    </dt>
                    <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">{{ __('app.feature_4_desc') }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- How it works --}}
    <div id="how-it-works" class="bg-gray-50 dark:bg-gray-800/50 py-24 transition-colors">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl lg:text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">{{ __('app.how_it_works') }}</h2>
            </div>
            <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                <div class="grid grid-cols-1 gap-8 sm:grid-cols-2">
                    @foreach(['step1', 'step2', 'step3', 'step4'] as $i => $step)
                    <div class="rounded-2xl bg-white dark:bg-gray-800 p-8 shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 card-hover animate-slide-in-up stagger-{{ $i + 1 }} transition-colors">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-lg">{{ $i + 1 }}</div>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">{{ __("app.{$step}_title") }}</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ __("app.{$step}_desc") }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="bg-indigo-950 py-12">
        <div class="mx-auto max-w-7xl px-6 lg:px-8 text-center">
            <p class="text-sm text-indigo-300">&copy; {{ date('Y') }} {{ __('app.app_name') }}. {{ __('app.tagline') }}.</p>
        </div>
    </footer>
</body>
</html>
