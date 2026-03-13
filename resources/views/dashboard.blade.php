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
            <div class="flex flex-wrap justify-center gap-1.5 sm:gap-2 mt-2">
                {{-- <a href="{{ route('dashboard') }}"
                    class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-blue-700 rounded-lg focus:outline-hidden focus:blue-cyan-700">
                    <i class="bi bi-house"></i>
                    Home
                </a> --}}
                <a href="{{ route('main') }}" title="Analytics and insights"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-speedometer"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/dashboard-layout.png"
                        alt="dashboard-layout" />
                    <span class="text-xs mt-1">Dashboard</span>
                </a>
                <a href="{{ route('metrics.index') }}" title="Deployment, recruitment & billing analytics"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-bar-chart-fill"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/96/combo-chart--v1.png"
                        alt="combo-chart--v1" />
                    <span class="text-xs mt-1">Metrics</span>
                </a>
                {{-- <a class="py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700" href="#">
                    <i class="bi bi-card-list"></i>
                    Item Management
                </a> --}}
                <a href="{{ route('job.index') }}" title="Manage job openings and requirements"
                    class=" py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-suitcase-lg"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/suitcase.png"
                        alt="suitcase" />
                    <span class="text-xs mt-1">Jobs</span>
                </a>
                <a href="{{ route('matching.index') }}" title="Upload and match resumes with job requirements"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-openai"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/artificial-intelligence.png"
                        alt="artificial-intelligence" />
                    <span class="text-xs mt-1">AI Matching</span>
                </a>
                <a href="{{ route('appointment.index') }}" title="Manage interviews and meetings with candidates"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-calendar-event"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/calendar.png"
                        alt="calendar" />
                    <span class="text-xs mt-1">Appointments</span>
                </a>
                <a href="{{ route('deployment.index') }}" title="Track and Manage deployed applicants to companies"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-person-check-fill"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/permanent-job.png"
                        alt="permanent-job" />
                    <span class="text-xs mt-1">Deployments</span>
                </a>
                <a href="{{ route('company.index') }}" title="Manage company profile and information"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    <img width="48" height="48" src="https://img.icons8.com/color/48/building.png"
                        alt="building" />
                    <span class="text-xs mt-1">Companies</span>
                </a>
                <a href="{{ route('billing.index') }}" title="Manage Billing and Invoicing"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-receipt"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/receipt.png"
                        alt="receipt" />
                    <span class="text-xs mt-1">Billing</span>
                </a>
                <a href="{{ route('reports.index') }}" title="Collection of various reports for analysis and monitoring"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-bar-chart"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/bar-chart.png"
                        alt="bar-chart" />
                    <span class="text-xs mt-1">Reports</span>
                </a>
                <a href="{{ route('setting.index') }}" title="AI Matching criteria and configuration"
                    class="py-1.5 px-2.5 flex flex-col items-center gap-x-1.5 text-sm text-gray-800 bg-gray-100 hover:text-cyan-700 rounded-lg focus:outline-hidden focus:text-cyan-700">
                    {{-- <i class="bi bi-gear"></i> --}}
                    <img width="48" height="48" src="https://img.icons8.com/color/48/gear.png" alt="gear" />
                    <span class="text-xs mt-1">Settings</span>
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
