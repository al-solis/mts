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
                {{-- <a href="{{ route('dashboard') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-blue-700 rounded-lg focus:outline-hidden focus:blue-cyan-700">
                    <i class="bi bi-house"></i>
                    Home
                </a> --}}
                <a href="{{ route('main') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-speedometer"></i>
                    Dashboard
                </a>
                <a href="{{ route('metrics.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-bar-chart-fill"></i>
                    Metrics
                </a>
                {{-- <a class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700" href="#">
                    <i class="bi bi-card-list"></i>
                    Item Management
                </a> --}}
                <a href="{{ route('job.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-suitcase-lg"></i>
                    Jobs
                </a>
                <a href="{{ route('matching.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-openai"></i>
                    AI Matching
                </a>
                <a href="{{ route('appointment.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-calendar-event"></i>
                    Appointments
                </a>
                <a href="{{ route('deployment.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-person-check-fill"></i>
                    Deployment
                </a>
                <a href="{{ route('company.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-building"></i>
                    Companies
                </a>
                <a href="{{ route('billing.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-receipt"></i>
                    Billing
                </a>
                <a class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700"
                    href="{{ route('reports.index') }}">
                    <i class="bi bi-bar-chart"></i>
                    Reports
                </a>
                <a href="{{ route('setting.index') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <i class="bi bi-gear"></i>
                    Settings
                </a>
            </div>
        </div>
    </x-slot>

    <main>
        @yield('content')
    </main>
    <div class="flex justify-center">
        <footer class="w-full lg:max-w-4xl max-w-[335px] text-xs mt-6 not-has-[nav]:hidden">
            <p class="text-center mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                All rights reserved &copy; {{ date('Y') }}. This project is maintained and developed by
                <a href="https://sanai.ph" target="_blank"
                    class="font-medium underline underline-offset-4 text-gray-600 dark:text-gray-400">SANAI Digital
                    Solutions Philippines Inc.</a> v{{ config('app.version') }}
            </p>
        </footer>
    </div>

</x-app-layout>
