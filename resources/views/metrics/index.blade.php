@extends('dashboard')
@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Metrics Dashboard</h1>
                <p class="text-sm text-gray-500">Deployment & Recruitment Analytics</p>
            </div>

            {{-- Date Range --}}
            <div class="flex gap-3">
                <input type="date" id="start_date"
                    class="border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-200">
                <input type="date" id="end_date"
                    class="border rounded-lg px-3 py-2 text-sm shadow-sm focus:ring focus:ring-blue-200">
            </div>
        </div>

        {{-- ================= SUMMARY CARDS ================= --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">

            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-sm text-gray-500">Total Deployments</p>
                <h2 id="totalDeployments" class="text-2xl font-bold text-blue-600">0</h2>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-sm text-gray-500">Companies Served</p>
                <h2 id="companiesServed" class="text-2xl font-bold text-indigo-600">0</h2>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-sm text-gray-500">Conversion Rate</p>
                <h2 id="conversionRate" class="text-2xl font-bold text-green-600">0%</h2>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-sm text-gray-500">Avg Time to Deploy</p>
                <h2 id="avgTimeToDeploy" class="text-2xl font-bold text-yellow-600">0 days</h2>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-sm text-gray-500">Total Applicants</p>
                <h2 id="totalApplicants" class="text-2xl font-bold text-purple-600">0</h2>
            </div>

            <div class="bg-white rounded-xl shadow p-4">
                <p class="text-sm text-gray-500">Interviews Scheduled</p>
                <h2 id="totalInterviews" class="text-2xl font-bold text-pink-600">0</h2>
            </div>

        </div>

        {{-- ================= CHART SECTION ================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Billed vs Payment per Company --}}
            <div class="bg-white rounded-xl shadow p-6 sm:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Billed vs Payment per Company</h3>
                <canvas id="billingCompanyChart" class="w-full h-full"></canvas>
            </div>

            {{-- Deployments Over Time --}}
            {{-- <div class="bg-white rounded-xl shadow p-6 sm:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Deployments Over Time</h3>
                <canvas id="dailyChart" class="w-full h-full"></canvas>
            </div> --}}

            {{-- Top Companies --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Top Companies by Deployments</h3>
                <canvas id="companyChart" class="w-full h-full"></canvas>
            </div>

            {{-- Industry Distribution --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Deployments by Industry</h3>
                <canvas id="industryChart" class="w-full h-full"></canvas>
            </div>

            {{-- Monthly Trends --}}
            <div class="bg-white rounded-xl shadow p-6 sm:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Monthly Deployment Trends</h3>
                <canvas id="monthlyChart" class="w-full h-full"></canvas>
            </div>

        </div>

        {{-- ================= TOP PERFORMERS ================= --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Top Performing Companies</h3>
            <div id="topPerformers" class="divide-y"></div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        function formatDateForInput(dateString) {
            if (!dateString) return '';

            const date = new Date(dateString);
            if (isNaN(date)) return '';

            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        document.addEventListener('DOMContentLoaded', function() {

            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

            document.getElementById('start_date').value = formatDateForInput(firstDay);
            document.getElementById('end_date').value = formatDateForInput(today);

            loadMetrics();

            document.getElementById('start_date').addEventListener('change', loadMetrics);
            document.getElementById('end_date').addEventListener('change', loadMetrics);

        });

        let lineChart, barChart, pieChart, monthlyChart, billingCompanyChart;

        function loadMetrics() {

            const start = document.getElementById('start_date').value;
            const end = document.getElementById('end_date').value;

            fetch(`/metrics/data?start_date=${start}&end_date=${end}`)
                .then(res => res.json())
                .then(data => {

                    const s = data.summary;

                    // =============================
                    // UPDATE CARDS
                    // =============================
                    document.getElementById('totalDeployments').innerText = s.totalDeployments;
                    document.getElementById('companiesServed').innerText = s.uniqueCompanies;
                    document.getElementById('conversionRate').innerText = s.conversionRate + '%';
                    document.getElementById('avgTimeToDeploy').innerText = s.avgTimeToDeploy + ' days';
                    document.getElementById('totalApplicants').innerText = s.totalApplicants;
                    document.getElementById('totalInterviews').innerText = s.totalInterviews;

                    // =============================
                    // BILLING VS PAYMENT BAR CHART
                    // =============================
                    if (billingCompanyChart) billingCompanyChart.destroy();

                    billingCompanyChart = new Chart(document.getElementById('billingCompanyChart'), {
                        type: 'bar',
                        data: {
                            labels: data.billing.map(i => i.name),
                            datasets: [{
                                    label: 'Total Billed',
                                    data: data.billing.map(i => i.total_billed),
                                    backgroundColor: '#3b82f6'
                                },
                                {
                                    label: 'Total Paid',
                                    data: data.billing.map(i => i.total_paid),
                                    backgroundColor: '#22c55e'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                x: {
                                    stacked: false
                                },
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // =============================
                    // DAILY LINE CHART
                    // =============================
                    // if (lineChart) lineChart.destroy();

                    // lineChart = new Chart(document.getElementById('dailyChart'), {
                    //     type: 'line',
                    //     data: {
                    //         labels: data.dailyDeployments.map(i => i.date),
                    //         datasets: [{
                    //             label: 'Daily Deployments',
                    //             data: data.dailyDeployments.map(i => i.total),
                    //             borderColor: '#3b82f6',
                    //             fill: false
                    //         }]
                    //     }
                    // });

                    // =============================
                    // TOP COMPANIES BAR
                    // =============================                    

                    if (barChart) barChart.destroy();

                    const ctx = document.getElementById('companyChart').getContext('2d');

                    const total = data.topCompanies.length;

                    // Generate gradient colors automatically
                    const gradients = data.topCompanies.map((c, i) => {

                        const hue = Math.round((360 / total) * i);

                        const gradient = ctx.createLinearGradient(0, 0, 0, 400);

                        gradient.addColorStop(0, `hsl(${hue}, 75%, 60%)`);
                        gradient.addColorStop(1, `hsl(${hue}, 75%, 40%)`);

                        return gradient;
                    });

                    barChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.topCompanies.map(c => c.name),
                            datasets: [{
                                label: 'Deployments',
                                data: data.topCompanies.map(c => c.deployments_count),
                                backgroundColor: gradients,
                                borderRadius: 6,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // =============================
                    // INDUSTRY PIE
                    // =============================
                    if (pieChart) pieChart.destroy();

                    pieChart = new Chart(document.getElementById('industryChart'), {
                        type: 'pie',
                        data: {
                            labels: Object.keys(data.industryData),
                            datasets: [{
                                data: Object.values(data.industryData),
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true, // fills the div height
                            plugins: {
                                legend: {
                                    position: 'right'
                                }
                            }
                        }
                    });

                    // =============================
                    // MONTHLY BAR
                    // =============================
                    // if (monthlyChart) monthlyChart.destroy();

                    // monthlyChart = new Chart(document.getElementById('monthlyChart'), {
                    //     type: 'bar',
                    //     data: {
                    //         labels: data.monthlyDeployments.map(m => {
                    //             const date = new Date(m.year, m.month - 1);
                    //             return date.toLocaleString('default', {
                    //                 month: 'short',
                    //                 year: 'numeric'
                    //             });
                    //         }),
                    //         datasets: [{
                    //             label: 'Deployments',
                    //             data: data.monthlyDeployments.map(m => m.total),
                    //             backgroundColor: '#14b8a6'
                    //         }]
                    //     }
                    // });

                    if (monthlyChart) monthlyChart.destroy();

                    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');

                    const months = data.monthlyDeployments.length;

                    const monthlyGradients = data.monthlyDeployments.map((m, i) => {

                        const hue = Math.round((360 / months) * i);

                        const gradient = monthlyCtx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, `hsl(${hue}, 75%, 65%)`);
                        gradient.addColorStop(1, `hsl(${hue}, 75%, 45%)`);

                        return gradient;
                    });

                    monthlyChart = new Chart(monthlyCtx, {
                        type: 'bar',
                        data: {
                            labels: data.monthlyDeployments.map(m => {
                                const date = new Date(m.year, m.month - 1);
                                return date.toLocaleString('default', {
                                    month: 'short',
                                    year: 'numeric'
                                });
                            }),
                            datasets: [{
                                label: 'Deployments',
                                data: data.monthlyDeployments.map(m => m.total),
                                backgroundColor: monthlyGradients,
                                borderRadius: 6,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // =============================
                    // TOP PERFORMERS LIST
                    // =============================
                    let performerHtml = '';
                    data.topPerformers.forEach((c, index) => {
                        performerHtml += `
                    <div class="flex justify-between p-2 border-b">
                        <div>
                            <strong>${index+1}. ${c.name}</strong>
                            <div class="text-sm">${c.deployments} deployed / ${c.applicants} applicants</div>
                        </div>
                        <div class="font-bold">${c.success}% success</div>
                    </div>
                `;
                    });

                    document.getElementById('topPerformers').innerHTML = performerHtml;
                });
        }

        document.getElementById('start_date').addEventListener('change', loadMetrics);
        document.getElementById('end_date').addEventListener('change', loadMetrics);
    </script>
@endsection
