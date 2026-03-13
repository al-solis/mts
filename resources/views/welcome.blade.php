<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/preline.js', 'resources/js/flowbite.min.js'])
        {{-- @else
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('js/preline.js') }}" defer></script>
        <script src="{{ asset('js/flowbite.min.js') }}" defer></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
    @endif
</head>

<body class="mt-6">

    <!-- Hero -->
    <div class="max-w-[85rem] mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Grid -->
        <div class="grid md:grid-cols-2 gap-4 md:gap-8 xl:gap-20 md:items-center">
            <div>
                <h1 class="block text-3xl font-bold text-foreground sm:text-4xl lg:text-6xl lg:leading-tight">Right
                    Talent, Right Job, Every Time.</span></h1>
                <p class="mt-3 text-lg text-foreground">Find the right talent faster and track workforce deployment
                    effortlessly. <strong>Manpower Tracking System</strong> leverages AI to match candidates with the
                    right jobs, scheduling, monitor billing,
                    and streamline manpower operations — all in one intelligent platform.</p>

                <!-- Buttons -->
                <div class="mt-7 grid gap-3 w-full sm:inline-flex">
                    @if (Route::has('login'))
                        @auth
                            <a class="py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-layer-line text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                                href="{{ url('/dashboard') }}">
                                Dashboard
                            </a>
                        @else
                            <a class="py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-blue border border-blue-line text-blue-foreground hover:bg-blue-hover focus:outline-hidden focus:bg-blue-focus disabled:opacity-50 disabled:pointer-events-none"
                                href="{{ route('login') }}">
                                Login
                                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </a>
                            <a class="py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-layer border border-layer-line text-layer-foreground shadow-2xs hover:bg-layer-hover focus:outline-hidden focus:bg-layer-focus disabled:opacity-50 disabled:pointer-events-none"
                                href="{{ route('register') }}">
                                Register
                            </a>
                        @endauth
                    @endif
                </div>
                <!-- End Buttons -->

            </div>
            <!-- End Col -->

            <div class="relative ms-4">
                <img class="w-full rounded-md" src="{{ asset('images/MTS.png') }}" alt="MTS">
                <div
                    class="absolute inset-0 -z-1 bg-linear-to-tr from-surface-1 via-transparent to-travia-transparent size-full rounded-md mt-4 -mb-4 me-4 -ms-4 lg:mt-6 lg:-mb-6 lg:me-6 lg:-ms-6">
                </div>

                <!-- SVG-->
                <div class="absolute bottom-0 start-0">
                    <svg class="w-2/3 ms-auto h-auto text-white dark:text-neutral-900" width="630" height="451"
                        viewBox="0 0 630 451" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="531" y="352" width="99" height="99" fill="currentColor" />
                        <rect x="140" y="352" width="106" height="99" fill="currentColor" />
                        <rect x="482" y="402" width="64" height="49" fill="currentColor" />
                        <rect x="433" y="402" width="63" height="49" fill="currentColor" />
                        <rect x="384" y="352" width="49" height="50" fill="currentColor" />
                        <rect x="531" y="328" width="50" height="50" fill="currentColor" />
                        <rect x="99" y="303" width="49" height="58" fill="currentColor" />
                        <rect x="99" y="352" width="49" height="50" fill="currentColor" />
                        <rect x="99" y="392" width="49" height="59" fill="currentColor" />
                        <rect x="44" y="402" width="66" height="49" fill="currentColor" />
                        <rect x="234" y="402" width="62" height="49" fill="currentColor" />
                        <rect x="334" y="303" width="50" height="49" fill="currentColor" />
                        <rect x="581" width="49" height="49" fill="currentColor" />
                        <rect x="581" width="49" height="64" fill="currentColor" />
                        <rect x="482" y="123" width="49" height="49" fill="currentColor" />
                        <rect x="507" y="124" width="49" height="24" fill="currentColor" />
                        <rect x="531" y="49" width="99" height="99" fill="currentColor" />
                    </svg>
                </div>
                <!-- End SVG-->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Grid -->
    </div>
    <!-- End Hero -->

    {{-- <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
        @if (Route::has('login'))
            <nav class="flex items-center justify-end gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] text-[#1b1b18] border border-transparent hover:border-[#19140035] dark:hover:border-[#3E3E3A] rounded-sm text-sm leading-normal">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal">
                            Register
                        </a>
                    @endif
                @endauth
            </nav>
        @endif
    </header>


    <div
        class="flex flex-col items-center justify-center w-full
                    transition-opacity opacity-100 duration-750 lg:grow
                    starting:opacity-0 bg-white p-6 rounded-lg">

        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-auto max-w-[280px] lg:max-w-sm">

        <p class="mt-4 text-center text-4xl font-medium text-gray-400 tracking-wide">
            Manpower Tracking System
        </p>

    </div>

    @if (Route::has('login'))
        <div class="h-14.5 hidden lg:block"></div>
    @endif --}}

</body>

</html>
