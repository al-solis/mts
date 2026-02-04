<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="flex-shrink-0 mt-0">
                <x-application-logo class="h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
            </div>

            <h2 class="ml-3 font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight mt-0">
                {{ __('Manpower Tracking System') }}
            </h2>
        </div>

        <div class="py-0.5">
            <div class="flex flex-wrap justify-center gap-1.5 sm:gap-2">
                <a href="{{ route('dashboard') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-blue-700 rounded-lg focus:outline-hidden focus:blue-cyan-700">
                    {{-- <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg> --}}
                    <i class="bi bi-house"></i>
                    Home
                </a>
                <a href=""
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-speedometer"></i>
                    Dashboard
                </a>
                <a href=""
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi bi-box"></i>
                    Assets
                </a>
                {{-- <a class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700" href="#">
                    <i class="bi bi-card-list"></i>
                    Item Management
                </a> --}}
                <a href=""
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-upc-scan"></i>
                    Scanner
                </a>
                <a href=""
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-card-text"></i>
                    Licenses
                </a>
                <a href=""
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-folder"></i>
                    Clearance
                </a>
                <a href=""
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-person"></i>
                    Employees
                </a>
                {{-- <a href="{{ route('location.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-building"></i>
                    Location
                </a> --}}
                <a class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700"
                    href="#">
                    <i class="bi bi-bar-chart"></i>
                    Reports
                </a>
                <a href=""
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-gear"></i>
                    Setup
                </a>
            </div>
        </div>
    </x-slot>

    <main>
        @yield('content')
    </main>

</x-app-layout>
