@extends('dashboard')
@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">

    <div class="p-6 space-y-6 bg-gray-50">

        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard Overview</h1>
            <p class="text-sm text-gray-500">AI-Powered Recruitment Analytics and Insights</p>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6">
            <div class="bg-white p-4 rounded-xl shadow flex flex-col justify-between">
                <div class="text-gray-500 text-sm">Total Applicants</div>
                <div class="text-2xl font-semibold">{{ $totalApplicants }}</div>
                <div class="text-xs text-gray-400">{{ $qualifiedApplicants }} qualified ({{ $minMatch }}%+)</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow">
                <div class="text-gray-500 text-sm">Active Jobs</div>
                <div class="text-2xl font-semibold">{{ $totalActiveJobs }}</div>
                <div class="text-xs text-gray-400">Open positions</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow">
                <div class="text-gray-500 text-sm">Partner Companies</div>
                <div class="text-2xl font-semibold">{{ $totalCompanies }}</div>
                <div class="text-xs text-gray-400">Client organizations</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow">
                <div class="text-gray-500 text-sm">Successful Placements</div>
                <div class="text-2xl font-semibold">{{ $totalPlacements }}</div>
                <div class="text-xs text-gray-400">Deployed to companies</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow">
                <div class="text-gray-500 text-sm">Pending Appointments</div>
                <div class="text-2xl font-semibold">{{ $pendingAppointments }}</div>
                <div class="text-xs text-gray-400">Scheduled interviews</div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow">
                <div class="text-gray-500 text-sm">Average Match Score</div>
                <div class="text-2xl font-semibold">{{ number_format($aveMatchScore, 1) }}%</div>
                <div class="text-xs text-gray-400">AI matching accuracy</div>
            </div>
        </div>

        <!-- Recruitment Pipeline -->
        <div class="bg-white p-6 rounded-xl shadow">
            <h3 class="text-lg font-semibold mb-4">Recruitment Pipeline</h3>
            <div class="space-y-4">
                @php
                    $totalForPipeline = max($totalApplicants, 1); // Prevent division by zero
                @endphp

                <!-- Pending Review -->
                <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Pending Review</span>
                        <span class="font-medium">{{ $pendingApplicants }}</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden">
                        @php
                            $pendingPercentage = ($pendingApplicants / $totalForPipeline) * 100;
                            $pendingColor = match (true) {
                                $pendingApplicants > 50 => 'bg-red-600',
                                $pendingApplicants > 30 => 'bg-orange-500',
                                $pendingApplicants > 10 => 'bg-yellow-500',
                                default => 'bg-purple-900',
                            };
                        @endphp
                        <div class="{{ $pendingColor }} h-2.5 rounded-full transition-all duration-500"
                            style="width: {{ $pendingPercentage }}%"></div>
                    </div>
                    @if ($pendingApplicants > 50)
                        <p class="text-xs text-red-600 mt-1">⚠️ High volume needs attention</p>
                    @endif
                </div>

                <!-- Qualified Candidates -->
                <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Qualified Candidates ({{ $minMatch }}%+ match)</span>
                        <span class="font-medium">{{ $qualifiedApplicants }}</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden">
                        @php
                            $qualifiedPercentage = ($qualifiedApplicants / $totalForPipeline) * 100;
                            $qualifiedColor = match (true) {
                                $qualifiedPercentage >= 70 => 'bg-green-600',
                                $qualifiedPercentage >= 50 => 'bg-blue-900',
                                $qualifiedPercentage >= 30 => 'bg-yellow-500',
                                default => 'bg-red-600',
                            };
                        @endphp
                        <div class="{{ $qualifiedColor }} h-2.5 rounded-full transition-all duration-500"
                            style="width: {{ $qualifiedPercentage }}%"></div>
                    </div>
                    @if ($qualifiedPercentage < 30)
                        <p class="text-xs text-red-600 mt-1">⚠️ Low qualification rate</p>
                    @endif
                </div>

                <!-- Successfully Deployed -->
                <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>Successfully Deployed</span>
                        <span class="font-medium">{{ $totalPlacements }}</span>
                    </div>
                    <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden">
                        @php
                            $deployedPercentage = ($totalPlacements / $totalForPipeline) * 100;
                            $deployedColor = match (true) {
                                $deployedPercentage >= 50 => 'bg-green-600',
                                $deployedPercentage >= 30 => 'bg-blue-900',
                                $deployedPercentage >= 15 => 'bg-yellow-500',
                                default => 'bg-red-600',
                            };
                        @endphp
                        <div class="{{ $deployedColor }} h-2.5 rounded-full transition-all duration-500"
                            style="width: {{ $deployedPercentage }}%"></div>
                    </div>
                    @if ($deployedPercentage < 15)
                        <p class="text-xs text-red-600 mt-1">⚠️ Low deployment rate</p>
                    @endif
                </div>
            </div>

            <!-- Pipeline Summary Stats -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <span class="text-xs text-gray-500 block">Conversion Rate</span>
                        <span class="text-lg font-semibold text-gray-800">
                            {{ $totalApplicants > 0 ? number_format(($totalPlacements / $totalApplicants) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Qualification Rate</span>
                        <span class="text-lg font-semibold text-gray-800">
                            {{ $totalApplicants > 0 ? number_format(($qualifiedApplicants / $totalApplicants) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 block">Placement Rate</span>
                        <span class="text-lg font-semibold text-gray-800">
                            {{ $qualifiedApplicants > 0 ? number_format(($totalPlacements / $qualifiedApplicants) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Matches & Recent Activity -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-semibold mb-4">Top Matches</h3>
                <ul class="space-y-3 text-sm">
                    @forelse($topMatches as $match)
                        <li>
                            <div class="flex justify-between mb-1">
                                <span
                                    class="truncate max-w-[200px]">{{ $match->applicant_name ?? 'Resume ' . $match->id }}</span>
                                <span class="font-medium">{{ number_format($match->match_percentage, 0) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                                @php
                                    $matchColor = match (true) {
                                        $match->match_percentage >= 90 => 'bg-green-600',
                                        $match->match_percentage >= 80 => 'bg-blue-900',
                                        $match->match_percentage >= 70 => 'bg-yellow-500',
                                        default => 'bg-gray-600',
                                    };
                                @endphp
                                <div class="{{ $matchColor }} h-2 rounded-full transition-all duration-500"
                                    style="width: {{ $match->match_percentage }}%"></div>
                            </div>
                        </li>
                    @empty
                        <li class="text-gray-500 text-center py-4">No matches available</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white p-6 rounded-xl shadow">
                <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                <ul class="space-y-3 text-sm text-gray-600">
                    @forelse($recentActivities as $activity)
                        <li class="flex items-start">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mt-2 mr-2 flex-shrink-0"></span>
                            <span>{{ $activity }}</span>
                        </li>
                    @empty
                        <li class="text-gray-500 text-center py-4">No recent activity</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white p-6 rounded-xl shadow grid grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Post New Job -->
            <button onclick="window.location.href='{{ route('job.index') }}'"
                class="p-4 bg-blue-100 hover:bg-blue-200 rounded-lg transition-colors duration-200 font-medium text-sm flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M5 2a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2h3.5A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5H14a.5.5 0 0 1-1 0H3a.5.5 0 0 1-1 0h-.5A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2zm1 0h4a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1M1.5 3a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5H3V3zM15 12.5v-9a.5.5 0 0 0-.5-.5H13v10h1.5a.5.5 0 0 0 .5-.5m-3 .5V3H4v10z" />
                    </svg>
                </div>
                <p class="font-semibold text-gray-900">Post New Job</p>
            </button>

            <!-- Upload Resumes -->
            <button onclick="window.location.href='{{ route('matching.index') }}'"
                class="p-4 bg-green-100 hover:bg-green-200 rounded-lg transition-colors duration-200 font-medium text-sm flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                        <path
                            d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z" />
                    </svg>
                </div>
                <p class="font-semibold text-gray-900">Upload Resumes</p>
            </button>

            <!-- Schedule Interview -->
            <button onclick="window.location.href='{{ route('appointment.index') }}'"
                class="p-4 bg-purple-100 hover:bg-purple-200 rounded-lg transition-colors duration-200 font-medium text-sm flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z" />
                        <path
                            d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                    </svg>
                </div>
                <p class="font-semibold text-gray-900">Schedule Interview</p>
            </button>

            <!-- Deploy Applicant -->
            <button onclick="window.location.href='{{ route('deployment.index') }}'"
                class="p-4 bg-orange-100 hover:bg-orange-200 rounded-lg transition-colors duration-200 font-medium text-sm flex flex-col items-center justify-center text-center">
                <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center mb-2">
                    <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0" />
                        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6" />
                    </svg>
                </div>
                <p class="font-semibold text-gray-900">Deploy Applicant</p>
            </button>
        </div>

    </div>

    <!-- Optional: Add a small script for dynamic updates if needed -->
    <script>
        // You can add any JavaScript for real-time updates here
        // For example, auto-refresh data every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
    </script>
@endsection
