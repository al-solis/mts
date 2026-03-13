@extends('dashboard')
@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Metrics Dashboard</h1>
                <p class="text-sm text-gray-500">Deployment, Recruitment & Billing Analytics</p>
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
                <div id="billingCompanyChart" class="w-full h-full"></div>
            </div>

            {{-- Deployments Over Time --}}
            {{-- <div class="bg-white rounded-xl shadow p-6 sm:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Deployments Over Time</h3>
                <canvas id="dailyChart" class="w-full h-full"></canvas>
            </div> --}}

            {{-- Top Companies --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Top Companies by Deployments</h3>
                <div id="companyChart" class="w-full h-full"></div>
            </div>

            {{-- Industry Distribution --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Deployments by Industry</h3>
                {{-- <canvas id="industryChart" class="w-full h-full"></canvas> --}}
                <div id="industryChart" class="w-full h-full"></div>
            </div>

            {{-- Monthly Trends --}}
            <div class="bg-white rounded-xl shadow p-6 sm:col-span-2">
                <h3 class="text-lg font-semibold mb-4">Monthly Deployment Trends</h3>
                {{-- <canvas id="monthlyChart" class="w-full h-full"></canvas> --}}
                <div id="monthlyChart" class="w-full h-full"></div>
            </div>

        </div>

        {{-- ================= TOP PERFORMERS ================= --}}
        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Top Performing Companies</h3>
            <div id="topPerformers" class="divide-y"></div>
        </div>

    </div>

    {{-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0"></script>

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

        function generateColors(count) {

            const palette = [
                '#003396', '#1750AC', '#3373C4',
                '#5494DA', '#73B9EE', '#86CEFA',
                '#02BEFD', '#56CAFB', '#76D3FE'
            ];

            return Array.from({
                    length: count
                }, (_, i) =>
                palette[i % palette.length]
            );
        }

        function generate2ndColors(count) {

            const palette = [
                '#004c6d',
                '#255e7e',
                '#3d708f',
                '#5383a1',
                '#6996b3',
                '#7faac6',
                '#94bed9',
                '#abd2ec',
                '#c1e7ff'
            ];

            return Array.from({
                    length: count
                }, (_, i) =>
                palette[i % palette.length]
            );
        }



        let lineChart, barChart, pieChart, monthlyChart, billingCompanyChart;
        let industryChart;

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
                    // if (billingCompanyChart) billingCompanyChart.destroy();

                    // billingCompanyChart = new Chart(document.getElementById('billingCompanyChart'), {
                    //     type: 'bar',
                    //     data: {
                    //         labels: data.billing.map(i => i.name),
                    //         datasets: [{
                    //                 label: 'Total Billed',
                    //                 data: data.billing.map(i => i.total_billed),
                    //                 backgroundColor: '#3b82f6'
                    //             },
                    //             {
                    //                 label: 'Total Paid',
                    //                 data: data.billing.map(i => i.total_paid),
                    //                 backgroundColor: '#22c55e'
                    //             }
                    //         ]
                    //     },
                    //     options: {
                    //         responsive: true,
                    //         plugins: {
                    //             legend: {
                    //                 position: 'top'
                    //             }
                    //         },
                    //         scales: {
                    //             x: {
                    //                 stacked: false
                    //             },
                    //             y: {
                    //                 beginAtZero: true
                    //             }
                    //         }
                    //     }
                    // });

                    // Destroy previous chart if exists
                    // Destroy previous chart if exists
                    if (billingCompanyChart) {
                        billingCompanyChart.destroy();
                        billingCompanyChart = null;
                    }

                    // ApexCharts options
                    const BillingOptions = {
                        chart: {
                            type: "bar",
                            height: 320,
                            fontFamily: "Inter, sans-serif",
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                                name: "Total Billed",
                                data: data.billing.map(i => i.total_billed)
                            },
                            {
                                name: "Total Paid",
                                data: data.billing.map(i => i.total_paid)
                            }
                        ],
                        colors: ['#3b82f6', '#22c55e'],
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: "70%",
                                borderRadius: 8,
                                borderRadiusApplication: "end"
                            }
                        },
                        xaxis: {
                            categories: data.billing.map(i => i.name),
                            labels: {
                                style: {
                                    fontFamily: "Inter, sans-serif"
                                }
                            },
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            }
                        },
                        yaxis: {
                            labels: {
                                formatter: function(val) {
                                    return val.toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                },
                                style: {
                                    fontFamily: "Inter, sans-serif"
                                }
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            style: {
                                fontFamily: "Inter, sans-serif"
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            show: true
                        },
                        states: {
                            hover: {
                                filter: {
                                    type: "darken",
                                    value: 1
                                }
                            }
                        },
                        fill: {
                            opacity: 1
                        },
                        grid: {
                            show: false,
                            strokeDashArray: 4,
                            padding: {
                                left: 2,
                                right: 2,
                                top: -14
                            }
                        }
                    };

                    // Render chart
                    billingCompanyChart = new ApexCharts(
                        document.getElementById("billingCompanyChart"),
                        BillingOptions
                    );

                    billingCompanyChart.render();
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

                    // if (barChart) barChart.destroy();

                    // const ctx = document.getElementById('companyChart').getContext('2d');

                    // const total = data.topCompanies.length;

                    // // Generate gradient colors automatically
                    // const gradients = data.topCompanies.map((c, i) => {

                    //     const hue = Math.round((360 / total) * i);

                    //     const gradient = ctx.createLinearGradient(0, 0, 0, 400);

                    //     gradient.addColorStop(0, `hsl(${hue}, 75%, 60%)`);
                    //     gradient.addColorStop(1, `hsl(${hue}, 75%, 40%)`);

                    //     return gradient;
                    // });

                    // barChart = new Chart(ctx, {
                    //     type: 'bar',
                    //     data: {
                    //         labels: data.topCompanies.map(c => c.name),
                    //         datasets: [{
                    //             label: 'Deployments',
                    //             data: data.topCompanies.map(c => c.deployments_count),
                    //             backgroundColor: gradients,
                    //             borderRadius: 6,
                    //             borderSkipped: false
                    //         }]
                    //     },
                    //     options: {
                    //         responsive: true,
                    //         plugins: {
                    //             legend: {
                    //                 display: false
                    //             }
                    //         },
                    //         scales: {
                    //             y: {
                    //                 beginAtZero: true
                    //             }
                    //         }
                    //     }
                    // });

                    if (barChart) {
                        barChart.destroy();
                        barChart = null;
                    }

                    const randomColors = data.topCompanies.map(() => {
                        const r = Math.floor(Math.random() * 256);
                        const g = Math.floor(Math.random() * 256);
                        const b = Math.floor(Math.random() * 256);
                        return `rgb(${r}, ${g}, ${b})`;
                    });

                    const CompanyOptions = {
                        chart: {
                            type: "bar",
                            height: 320,
                            fontFamily: "Inter, sans-serif",
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            name: data.topCompanies.length > 0 ? "Deployments" : "No Data",
                            data: data.topCompanies.map(c => c.deployments_count)
                        }],
                        colors: randomColors,
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: "70%",
                                borderRadius: 8,
                                borderRadiusApplication: "end"
                            }
                        },
                        xaxis: {
                            categories: data.topCompanies.map(c => c.name),
                            labels: {
                                style: {
                                    fontFamily: "Inter, sans-serif"
                                }
                            },
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontFamily: "Inter, sans-serif"
                                }
                            }
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            style: {
                                fontFamily: "Inter, sans-serif"
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            show: true
                        },
                        states: {
                            hover: {
                                filter: {
                                    type: "darken",
                                    value: 1
                                }
                            }
                        },
                        fill: {
                            opacity: 1
                        },
                        grid: {
                            show: false,
                            strokeDashArray: 4,
                            padding: {
                                left: 2,
                                right: 2,
                                top: -14
                            }
                        }
                    };

                    // Render chart
                    barChart = new ApexCharts(
                        document.getElementById("companyChart"),
                        CompanyOptions
                    );

                    barChart.render();

                    // =============================
                    // INDUSTRY PIE
                    // =============================
                    // if (pieChart) pieChart.destroy();

                    // pieChart = new Chart(document.getElementById('industryChart'), {
                    //     type: 'pie',
                    //     data: {
                    //         labels: Object.keys(data.industryData),
                    //         datasets: [{
                    //             data: Object.values(data.industryData),
                    //         }]
                    //     },
                    //     options: {
                    //         responsive: true,
                    //         maintainAspectRatio: true, // fills the div height
                    //         plugins: {
                    //             legend: {
                    //                 position: 'right'
                    //             }
                    //         }
                    //     }
                    // });                    

                    const getNeutralPrimaryColor = () => {
                        const computedStyle = getComputedStyle(document.documentElement);
                        return computedStyle.getPropertyValue('--color-neutral-primary').trim() || "#E5E7EB";
                    };

                    const neutralPrimaryColor = getNeutralPrimaryColor();

                    if (industryChart) {
                        industryChart.destroy();
                    }

                    // convert your existing data
                    const labels = Object.keys(data.industryData);
                    const values = Object.values(data.industryData).map(Number);

                    const options = {
                        series: values,
                        labels: labels,
                        colors: generateColors(labels.length),
                        chart: {
                            height: 420,
                            width: "100%",
                            type: "pie",
                        },
                        stroke: {
                            colors: [neutralPrimaryColor],
                        },
                        plotOptions: {
                            pie: {
                                labels: {
                                    show: true
                                },
                                size: "100%",
                                dataLabels: {
                                    offset: -25
                                }
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val.toFixed(1) + "%";
                            },
                            style: {
                                fontFamily: "Inter, sans-serif",
                            }
                        },
                        legend: {
                            position: "right",
                            fontFamily: "Inter, sans-serif"
                        }
                    };

                    if (document.getElementById("industryChart") && typeof ApexCharts !== "undefined") {
                        industryChart = new ApexCharts(
                            document.getElementById("industryChart"),
                            options
                        );

                        industryChart.render();
                    }

                    // =============================
                    // MONTHLY AREA CHART
                    // =============================

                    const getBrandColor = () => {
                        const computedStyle = getComputedStyle(document.documentElement);
                        return computedStyle.getPropertyValue('--color-fg-brand').trim() || "#1447E6";
                    };

                    const brandColor = getBrandColor();

                    if (monthlyChart) monthlyChart.destroy();

                    // categories (months)
                    const categories = data.monthlyDeployments.map(m => {
                        const date = new Date(m.year, m.month - 1);
                        return date.toLocaleString('default', {
                            month: 'short',
                            year: 'numeric'
                        });
                    });

                    // values
                    const MonthlyDepValues = data.monthlyDeployments.map(m => Number(m.total));

                    const MonthlyDepOptions = {
                        chart: {
                            height: 350,
                            type: "area",
                            fontFamily: "Inter, sans-serif",
                            toolbar: {
                                show: false
                            }
                        },
                        series: [{
                            name: "Deployments",
                            data: MonthlyDepValues,
                            color: brandColor
                        }],
                        xaxis: {
                            categories: categories,
                            axisBorder: {
                                show: false
                            },
                            axisTicks: {
                                show: false
                            }
                        },
                        tooltip: {
                            enabled: true
                        },
                        fill: {
                            type: "gradient",
                            gradient: {
                                opacityFrom: 0.55,
                                opacityTo: 0,
                                gradientToColors: [brandColor]
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            width: 4
                        },
                        grid: {
                            strokeDashArray: 4,
                            padding: {
                                left: 2,
                                right: 2,
                                top: 0
                            }
                        },
                        yaxis: {
                            min: 0
                        }
                    };

                    monthlyChart = new ApexCharts(
                        document.querySelector("#monthlyChart"),
                        MonthlyDepOptions
                    );

                    monthlyChart.render();

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
