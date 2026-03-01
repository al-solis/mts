@extends('dashboard')
@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Reports</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Select a report to generate</p>
        </div>

        <!-- Reports Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Invoice Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-red-100 dark:bg-red-900 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-red-600 dark:text-red-300"
                                fill="none" width="16" height="16" stroke="currentColor" class="bi bi-receipt"
                                viewBox="0 0 16 16">
                                <path
                                    d="M1.92.506a.5.5 0 0 1 .434.14L3 1.293l.646-.647a.5.5 0 0 1 .708 0L5 1.293l.646-.647a.5.5 0 0 1 .708 0L7 1.293l.646-.647a.5.5 0 0 1 .708 0L9 1.293l.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .708 0l.646.647.646-.647a.5.5 0 0 1 .801.13l.5 1A.5.5 0 0 1 15 2v12a.5.5 0 0 1-.053.224l-.5 1a.5.5 0 0 1-.8.13L13 14.707l-.646.647a.5.5 0 0 1-.708 0L11 14.707l-.646.647a.5.5 0 0 1-.708 0L9 14.707l-.646.647a.5.5 0 0 1-.708 0L7 14.707l-.646.647a.5.5 0 0 1-.708 0L5 14.707l-.646.647a.5.5 0 0 1-.708 0L3 14.707l-.646.647a.5.5 0 0 1-.801-.13l-.5-1A.5.5 0 0 1 1 14V2a.5.5 0 0 1 .053-.224l.5-1a.5.5 0 0 1 .367-.27m.217 1.338L2 2.118v11.764l.137.274.51-.51a.5.5 0 0 1 .707 0l.646.647.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.646.646.646-.646a.5.5 0 0 1 .708 0l.509.509.137-.274V2.118l-.137-.274-.51.51a.5.5 0 0 1-.707 0L12 1.707l-.646.647a.5.5 0 0 1-.708 0L10 1.707l-.646.647a.5.5 0 0 1-.708 0L8 1.707l-.646.647a.5.5 0 0 1-.708 0L6 1.707l-.646.647a.5.5 0 0 1-.708 0L4 1.707l-.646.647a.5.5 0 0 1-.708 0z" />
                                <path
                                    d="M3 4.5a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 1 1 0 1h-6a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5m8-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5" />
                            </svg>
                        </div>
                        <span
                            class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900 px-2 py-1 rounded">Invoice</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Invoice Report</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Generate summary invoice
                        report. Filtered by company, date range and status.
                    </p>
                    <button onclick="openReportModal('invoice')"
                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Generate Report
                    </button>
                </div>
            </div>

            <!-- Payment Card -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-600 dark:text-green-300"
                                fill="none" width="16" height="16" stroke="currentColor" class="bi bi-credit-card"
                                viewBox="0 0 16 16">
                                <path
                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z" />
                                <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z" />
                            </svg>
                        </div>
                        <span
                            class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900 px-2 py-1 rounded">Payment</span>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Payment Report</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Generate payment summary
                        report. Filtered by company, date range and status.
                    </p>
                    <button onclick="openReportModal('payment')"
                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Generate Report
                    </button>
                </div>
            </div>

        </div>


    </div>

    <!-- Parameter Modal -->
    <div id="reportModal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full">
        <div class="relative p-4 w-full max-w-2xl h-full md:h-auto">
            <!-- Modal content -->
            <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                <!-- Modal header -->
                <div class="flex justify-between items-center pb-4 mb-4 rounded-t border-b sm:mb-5 dark:border-gray-600">
                    <div>
                        <h3 id="modalTitle" class="text-lg font-semibold text-gray-900 dark:text-white">
                            Report Parameters
                        </h3>
                        <p id="modalDescription" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Set parameters to generate your report
                        </p>
                    </div>
                    <button type="button" onclick="closeReportModal()"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white">
                        <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <!-- Dynamic Form Container -->
                <div id="formContainer" class="overflow-y-auto max-h-[70vh]">
                    <!-- Forms will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        const reportConfigs = {
            'invoice': {
                title: 'Invoice Report',
                description: 'Select parameters for invoice report',
                form: `
        <form id="reportForm" class="space-y-4 ml-1 mr-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Due Date Range</label>
                    <select name="date_range" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Status</label>
                    <select name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="">All Status</option>
                        <option value="0">Unpaid</option>
                        <option value="1">Partial</option>
                        <option value="2">Paid</option>
                        <option value="3">Voided</option>
                        <option value="4">Overdue</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Company</label>
                    <select name="company_id" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="">All companies</option>
                        @foreach ($companies ?? [] as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                
            </div>

            <div id="customDateRange" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">From Date</label>
                    <input type="date" name="from_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">To Date</label>
                    <input type="date" name="to_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
            </div>            

            <!-- Add format selection like invoice report -->
            <div class="flex items-center space-x-4 mt-4">
                <div class="flex items-center">
                    <input type="radio" id="invoice_pdf" name="format" value="pdf" checked
                        class="w-4 h-4 text-gray-600 bg-gray-100 border-gray-300 focus:ring-gray-500">
                    <label for="invoice_pdf" class="ml-2 text-sm font-medium text-gray-900 dark:text-white">PDF</label>
                </div>                
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t dark:border-gray-600">
                <button type="button" onclick="closeReportModal()" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700">
                    Generate Report
                </button>
            </div>
        </form>
    `
            },
            'payment': {
                title: 'Payment Report',
                description: 'Select parameters for payment report',
                form: `
        <form id="reportForm" class="space-y-4 ml-1 mr-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Due Date Range</label>
                    <select name="date_range" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Company</label>
                    <select name="company_id" class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="">All companies</option>
                        @foreach ($companies ?? [] as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Payment Method</label>
                    <select name="method" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="">All payment methods</option>
                        <option value="1">Bank Transfer</option>
                        <option value="2">Credit Card</option>
                        <option value="3">Cash</option>
                        <option value="4">Check</option>
                        <option value="5">Online Payment</option>                        
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">Status</label>
                    <select name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="2">Voided</option>
                    </select>
                </div>

                
                
                
            </div>

            <div id="customDateRange" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">From Date</label>
                    <input type="date" name="from_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">To Date</label>
                    <input type="date" name="to_date" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div>
            </div>            

            <!-- Add format selection like payment report -->
            <div class="flex items-center space-x-4 mt-4">
                <div class="flex items-center">
                    <input type="radio" id="payment_pdf" name="format" value="pdf" checked
                        class="w-4 h-4 text-gray-600 bg-gray-100 border-gray-300 focus:ring-gray-500">
                    <label for="payment_pdf" class="ml-2 text-sm font-medium text-gray-900 dark:text-white">PDF</label>
                </div>                
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t dark:border-gray-600">
                <button type="button" onclick="closeReportModal()" 
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-gray-300 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" 
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-700 hover:bg-gray-800 rounded-lg focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700">
                    Generate Report
                </button>
            </div>
        </form>
    `
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            $(document).ready(function() {
                $('#company').select2({
                    placeholder: "Select company",
                    allowClear: true,
                    width: '100%'
                });

            });
        });

        // Modal functions
        function openReportModal(reportType) {
            const config = reportConfigs[reportType];
            if (!config) return;

            document.getElementById('modalTitle').textContent = config.title;
            document.getElementById('modalDescription').textContent = config.description;
            document.getElementById('formContainer').innerHTML = config.form;

            // Show modal
            document.getElementById('reportModal').classList.remove('hidden');
            document.getElementById('reportModal').classList.add('flex');

            // Add event listener for custom date range toggle if needed
            if (reportType === 'invoice' || reportType === 'payment') {
                const dateRangeSelect = document.querySelector('select[name="date_range"]');
                if (dateRangeSelect) {
                    dateRangeSelect.addEventListener('change', function() {
                        const customRange = document.getElementById('customDateRange');
                        customRange.classList.toggle('hidden', this.value !== 'custom');
                    });
                }
            }

            // Add form submit handler
            const form = document.getElementById('reportForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    generateReport(reportType, new FormData(this));
                });
            }
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.add('hidden');
            document.getElementById('reportModal').classList.remove('flex');
            document.getElementById('formContainer').innerHTML = '';
        }

        function generateReport(reportType, formData) {
            // Convert FormData to object
            const params = Object.fromEntries(formData.entries());

            // Build query string
            const queryString = new URLSearchParams(params).toString();

            //alert('Report parameters: ' + queryString); // For debugging

            // Generate report URL based on type
            let url;
            switch (reportType) {
                case 'invoice':
                    url = `/reports/invoice?${queryString}`;
                    break;

                case 'payment':
                    url = `/reports/payment?${queryString}`;
                    break;
            }

            // Open in new tab for PDF preview
            if (params.format === 'excel') {
                window.location.href = url; // Download Excel
            } else {
                window.open(url, '_blank'); // Preview PDF
            }

            closeReportModal();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reportModal');
            if (event.target === modal) {
                closeReportModal();
            }
        }
    </script>
@endsection
