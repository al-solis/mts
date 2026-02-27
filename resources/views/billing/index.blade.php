@extends('dashboard')
@section('content')
    @php
        use Illuminate\Support\Str;
        use Carbon\Carbon;
    @endphp

    <div class="p-6 space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Billing and Collection</h1>
                <p class="text-sm text-gray-500">
                    Manage company billing, invoices, and payments.
                </p>
            </div>
            <div class="flex items-center gap-2 mt-0">
                {{-- <a href="{{ route('setup.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-gray border border-gray-300 bg-gray-100 rounded-lg hover:bg-gray-200 ">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </a> --}}

                <button data-modal-target="add-modal" data-modal-toggle="add-modal"
                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Create Billing
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        @php
            $cards = [
                [
                    'title' => 'Total Billed',
                    'value' => $totalBilled,
                    'color' => 'blue',
                    'icon' => '₱',
                ],
                [
                    'title' => 'Total Collected',
                    'value' => $totalCollected,
                    'color' => 'green',
                    'icon' => '
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" width="16" height="16"
                        class="bi bi-check2-circle" viewBox="0 0 16 16">                            
                            <path d="M2.5 8a5.5 5.5 0 0 1 8.25-4.764.5.5 0 0 0 .5-.866A6.5 6.5 0 1 0 14.5 8a.5.5 0 0 0-1 0 5.5 5.5 0 1 1-11 0"/>
                        <path d="M15.354 3.354a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z"/>
                        </svg>',
                ],
                [
                    'title' => 'Outstanding Balance',
                    'value' => $outstandingBalance,
                    'color' => 'yellow',
                    'icon' => '
                        <svg class="w-5 h-5 text-yellow-600" width="16" height="16" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71z"/>
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16m7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0"/>
                        </svg>',
                ],
                [
                    'title' => 'Overdue Invoices',
                    'value' => $overdueInvoices,
                    'color' => 'red',
                    'icon' => '
                        <svg class="w-5 h-5 text-red-600" width="16" height="16" fill="currentColor" class="bi bi-exclamation-lg" viewBox="0 0 16 16">
                            <path d="M7.005 3.1a1 1 0 1 1 1.99 0l-.388 6.35a.61.61 0 0 1-1.214 0zM7 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0"/>
                        </svg>',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($cards as $card)
                <div class="bg-white border rounded-xl p-4 flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-lg bg-{{ $card['color'] }}-100 
                                flex items-center justify-center">
                        {!! $card['icon'] !!}
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">{{ $card['title'] }}</p>
                        <p class="text-xl font-semibold text-gray-900">
                            {{ number_format($card['value'], 2) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 pt-1">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 pt-1"
                data-success="true">
                {{ session('success') }}
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Clear form fields after successful submission
                    clearModalFields();
                });
            </script>
        @endif

        {{-- Filters --}}
        <form action="" method="GET">
            <div class="flex flex-col md:flex-row gap-2 text-xs md:text-sm">
                <div class="md:w-2/3 w-full">
                    <input type="text" id="simple-search" name="search" placeholder="Search by name or description..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        value = "{{ request()->query('search') }}" oninput="this.form.submit()">
                </div>

                <div class="md:w-2/3 w-full">
                    <select id="searchloc" name="searchloc"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        onchange="this.form.submit()">
                        <option value="" selected>All Companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}"
                                {{ request('searchloc') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:w-1/3 w-full">
                    <select id="status" name="status"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                        onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
            </div>
            <button type="submit"
                class="hidden mt-4 w-full shrink-0 rounded-lg bg-gray-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800 sm:mt-0 sm:w-auto">Search</button>
        </form>

        <!-- Tabs -->
        <ul class="flex flex-wrap text-sm font-medium text-center border-b border-gray-200" id="billingTabs"
            data-tabs-toggle="#billingTabsContent" role="tablist">

            <li class="me-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="companies-tab" data-tabs-target="#companies"
                    type="button" role="tab" aria-controls="companies" aria-selected="true">
                    Companies
                </button>
            </li>

            <li class="me-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg" id="invoices-tab" data-tabs-target="#invoices"
                    type="button" role="tab" aria-controls="invoices" aria-selected="false">
                    Invoices
                </button>
            </li>

        </ul>

        <!-- Tabs Content -->
        <div id="billingTabsContent">

            <!-- ===================== -->
            <!-- Companies Tab -->
            <!-- ===================== -->
            <div class="p-6" id="companies" role="tabpanel">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">Company</th>
                                <th class="px-6 py-3">Industry</th>
                                <th class="px-6 py-3">Location</th>
                                <th class="px-6 py-3">Active Placements</th>
                                <th class="px-6 py-3">Billing from Deployment</th>
                                <th class="px-6 py-3">Total Billed</th>
                                <th class="px-6 py-3">Total Paid</th>
                                <th class="px-6 py-3">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($companies as $company)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium">
                                        {{ $company->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $company->industry }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $company->location }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $company->active_deployments_count ?? 0 }}
                                    </td>
                                    <td class="px-6 py-4">
                                        ₱{{ number_format($company->total_agency_fee ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        ₱{{ number_format($company->total_billed ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-green-600">
                                        ₱{{ number_format($company->total_collected ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-red-600 font-medium">
                                        ₱{{ number_format($company->balance ?? 0, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-6 text-gray-500">
                                        No companies found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>


            <!-- ===================== -->
            <!-- Invoices Tab -->
            <!-- ===================== -->
            <div class="hidden p-6" id="invoices" role="tabpanel">

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">Invoice #</th>
                                <th class="px-6 py-3">Company</th>
                                <th class="px-6 py-3">Description</th>
                                <th class="px-6 py-3">Amount</th>
                                <th class="px-6 py-3">Paid</th>
                                <th class="px-6 py-3">Balance</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Due Date</th>
                                <th class="px-6 py-3">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse($invoices ?? [] as $invoice)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 font-medium">
                                        {{ $invoice->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $invoice->company->name }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ Str::limit($invoice->description, 50) }}
                                    <td class="px-6 py-4">
                                        ₱{{ number_format($invoice->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-green-700">
                                        ₱{{ number_format($invoice->payment, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-red-700">
                                        ₱{{ number_format($invoice->amount - $invoice->payment, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($invoice->status == 1)
                                            <span
                                                class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">Partial</span>
                                        @elseif($invoice->status == 2)
                                            <span
                                                class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Paid</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $invoice->invoice_date }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $invoice->due_date }}
                                    </td>
                                    <td class="px-6 py-4 flex items-center justify-center space-x-2">
                                        <button type="button" title="Edit invoice : {{ $invoice->invoice_number }}"
                                            data-modal-target="edit-modal" data-modal-toggle="edit-modal"
                                            data-id="{{ $invoice->id }}" data-company_id="{{ $invoice->company_id }}"
                                            data-company_name="{{ $invoice->company->name }}"
                                            data-invoice_number="{{ $invoice->invoice_number }}"
                                            data-invoice_date="{{ $invoice->invoice_date }}"
                                            data-description="{{ $invoice->description }}"
                                            data-amount="{{ $invoice->amount }}"
                                            data-due_date="{{ $invoice->due_date }}"
                                            data-payment_terms="{{ $invoice->payment_terms }}"
                                            data-billing_cycle="{{ $invoice->billing_cycle }}"
                                            data-payment_method="{{ $invoice->payment_method }}"
                                            data-status="{{ $invoice->status }}" onclick="editInvoice(this)"
                                            class="group flex space-x-1 text-gray-500 hover:text-blue-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path
                                                    d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                                <path fill-rule="evenodd"
                                                    d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                            </svg>
                                        </button>

                                        <button type="button" title="Pay invoice : {{ $invoice->invoice_number }}"
                                            data-modal-target="payment-modal" data-modal-toggle="payment-modal"
                                            data-id="{{ $invoice->id }}"
                                            data-invoice_number="{{ $invoice->invoice_number }}"
                                            data-invoice_date="{{ $invoice->invoice_date }}"
                                            data-company_name="{{ $invoice->company->name }}"
                                            data-amount="{{ $invoice->amount }}" data-payment="{{ $invoice->payment }}"
                                            data-payment_method="{{ $invoice->payment_method }}"
                                            data-balance="{{ $invoice->amount - $invoice->payment }}"
                                            onclick="payInvoice(this)"
                                            class="group flex space-x-1 text-gray-500 hover:text-green-600 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z" />
                                                <path
                                                    d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-gray-500">
                                        No invoices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Add invoice modal -->
            <div id="add-modal" tabindex="-1" aria-hidden="true"
                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-modal md:h-full">
                <div class="relative p-2 w-full max-w-md h-full md:h-auto">
                    <!-- Modal content -->
                    <div class="relative p-4 bg-white rounded-lg shadow dark:bg-gray-800 sm:p-5">
                        <div class="pb-4 mb-2 rounded-t border-b sm:mb-5 dark:border-gray-600">
                            <div class="flex justify-between items-center">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white">
                                    Create Invoice
                                </h3>

                                <button type="button"
                                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                    data-modal-toggle="add-modal">
                                    <svg aria-hidden="true" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="sr-only">Close modal</span>
                                </button>
                            </div>
                            {{-- <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Select a passed applicant to deploy them to a company.
                            </p> --}}

                        </div>

                        <!-- Modal body -->
                        <div class="overflow-y-auto max-h-[70vh]">
                            <form action="{{ route('billing.store') }}" method="POST">
                                @csrf
                                <div class="grid ml-1 mr-1 gap-2 mb-4 sm:grid-cols-2">
                                    <div class="w-full md:col-span-2">
                                        <label for="company_id"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Company
                                            Name*</label>
                                        <select name="company_id" id="company_id"
                                            class="select2 bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            required>
                                            <option value="" selected>Select company</option>
                                            @foreach ($companies as $company)
                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="description"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Description*</label>
                                        <textarea id="description" name="description" rows="3"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="e.g. Monthly manpower services - Jan 2026" required></textarea>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="amount"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Amount*</label>
                                        <input type="number" id="amount" name="amount"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="25000" required>
                                        </input>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="invoice_date"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Invoice
                                            Date*</label>
                                        <input type="date" name="invoice_date" id="invoice_date"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="mm/dd/yyyy" required>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="due_date"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Due
                                            Date*</label>
                                        <input type="date" name="due_date" id="due_date" min="{{ date('Y-m-d') }}"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="mm/dd/yyyy" required>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="payment_terms"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Terms*</label>
                                        <input type="number" name="payment_terms" id="payment_terms" min="0"
                                            value="30"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="30" required>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="billing_cycle"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Billing
                                            Cycle*</label>
                                        <select name="billing_cycle" id="billing_cycle"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="30" required>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Monthly" selected>Monthly</option>
                                            <option value="Quarterly">Quarterly</option>
                                            <option value="Annually">Annually</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="payment_method"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Method*</label>
                                        <select name="payment_method" id="payment_method"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="1000" required>
                                            <option value="1">Bank Transfer</option>
                                            <option value="2">Credit Card</option>
                                            <option value="3">Cash</option>
                                            <option value="4">Check</option>
                                            <option value="5">Online Payment</option>
                                        </select>
                                    </div>

                                </div>
                                <button type="submit"
                                    class="text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    <svg class="mr-1 -ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Create Invoice
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End schedule modal -->

            <!-- Modal  Edit-->
            <div id="edit-modal" tabindex="-1" aria-hidden="true"
                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative p-2 w-full max-w-md max-h-full">
                    <!-- Modal content -->
                    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                        <!-- Modal header -->
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Update Invoice
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-toggle="edit-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close</span>
                            </button>
                        </div>
                        <!-- Modal body -->
                        <div class="overflow-y-auto max-h-[70vh]">
                            <form id="editForm" class="p-4 md:p-5" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="edit_id" id="edit_id">
                                <div class="grid ml-1 mr-1 gap-2 mb-2 sm:grid-cols-2">
                                    <div class="w-full md:col-span-2">
                                        <label for="edit_invoice_number"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Invoice
                                            Number*</label>
                                        <input type="text" name="edit_invoice_number" id="edit_invoice_number"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            required>
                                        </input>
                                    </div>

                                    <div class="w-full md:col-span-2">
                                        <label for="edit_company_name"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Company
                                            Name*</label>
                                        <input type="text" name="edit_company_name" id="edit_company_name"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            required>
                                        </input>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="edit_description"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Description*</label>
                                        <textarea id="edit_description" name="edit_description" rows="3"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="e.g. Monthly manpower services - Jan 2026" required></textarea>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="edit_amount"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Amount*</label>
                                        <input type="number" id="edit_amount" name="edit_amount"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-500 focus:border-gray-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="25000" required>
                                        </input>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="edit_invoice_date"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Invoice
                                            Date*</label>
                                        <input type="date" name="edit_invoice_date" id="edit_invoice_date"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="mm/dd/yyyy" required>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="edit_due_date"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Due
                                            Date*</label>
                                        <input type="date" name="edit_due_date" id="edit_due_date"
                                            min="{{ date('Y-m-d') }}"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="mm/dd/yyyy" required>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="edit_payment_terms"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Terms*</label>
                                        <input type="number" name="edit_payment_terms" id="edit_payment_terms"
                                            min="0" value="30"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="30" required>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="edit_billing_cycle"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Billing
                                            Cycle*</label>
                                        <select name="edit_billing_cycle" id="edit_billing_cycle"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="30" required>
                                            <option value="Weekly">Weekly</option>
                                            <option value="Monthly" selected>Monthly</option>
                                            <option value="Quarterly">Quarterly</option>
                                            <option value="Annually">Annually</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="edit_payment_method"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Method*</label>
                                        <select name="edit_payment_method" id="edit_payment_method"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="1000" required>
                                            <option value="1">Bank Transfer</option>
                                            <option value="2">Credit Card</option>
                                            <option value="3">Cash</option>
                                            <option value="4">Check</option>
                                            <option value="5">Online Payment</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="mt-2 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    {{-- <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg> --}}
                                    Update Invoice
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End edit modal -->

            <!-- Payment  Modal-->
            <div id="payment-modal" tabindex="-1" aria-hidden="true"
                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative p-2 w-full max-w-md max-h-full">
                    <!-- Modal content -->
                    <div class="relative bg-white rounded-lg shadow-sm dark:bg-gray-700">
                        <!-- Modal header -->
                        <div
                            class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600 border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Receive Payment
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                data-modal-toggle="payment-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close</span>
                            </button>
                        </div>
                        <!-- Modal body -->
                        <div class="overflow-y-auto max-h-[70vh]">
                            <form id="paymentForm" class="p-4 md:p-5" method="POST">
                                @csrf
                                <input type="hidden" name="payment_id" id="payment_id">
                                <div class="grid ml-1 mr-1 gap-2 mb-2 sm:grid-cols-2">
                                    <div class="block text-xs font-medium text-gray-900 dark:text-white">
                                        Invoice #:
                                    </div>
                                    <div class="block text-xs font-semibold text-right text-gray-900 dark:text-white"
                                        id="pay_invoice_number" name="pay_invoice_number"></div>

                                    <div class="block text-xs font-medium text-gray-900 dark:text-white">
                                        Company:
                                    </div>
                                    <div class="block text-xs font-semibold text-right text-gray-900 dark:text-white"
                                        id="pay_company_name" name="pay_company_name"></div>

                                    <div class="block text-xs font-medium text-gray-900 dark:text-white">
                                        Total Amount:
                                    </div>
                                    <div class="block text-xs font-semibold text-right text-gray-900 dark:text-white"
                                        id="pay_total_amount" name="pay_total_amount"></div>

                                    <div class="block text-xs font-medium text-gray-900 dark:text-white">
                                        Already Paid:
                                    </div>
                                    <div class="block text-xs font-semibold text-right text-green-900 dark:text-white"
                                        id="pay_already_paid" name="pay_already_paid"></div>

                                    <div class="mt-2 block text-xs font-semibold border-t text-gray-900 dark:text-white">
                                        Outstanding Balance:
                                    </div>
                                    <div class="mt-2 block text-xs font-semibold text-right border-t text-red-900 dark:text-white"
                                        id="pay_outstanding_balance" name="pay_outstanding_balance"></div>

                                    <div class="sm:col-span-2">
                                        <label for="pay_amount"
                                            class="mt-2 block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Amount*</label>
                                        <input type="number" name="pay_amount" id="pay_amount" min="0"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="1000" required>
                                        </input>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="pay_date"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Date*</label>
                                        <input type="date" name="pay_date" id="pay_date"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="mm/dd/yyyy" required>
                                        </input>
                                    </div>

                                    <div class="sm:col-span-1">
                                        <label for="pay_payment_method"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Method*</label>
                                        <select name="pay_payment_method" id="pay_payment_method"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="1000" required>
                                            <option value="1">Bank Transfer</option>
                                            <option value="2">Credit Card</option>
                                            <option value="3">Cash</option>
                                            <option value="4">Check</option>
                                            <option value="5">Online Payment</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="pay_reference"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Reference*</label>
                                        <input type="text" name="pay_reference" id="pay_reference"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="e.g. Check no./ bank transfer reference">
                                        </input>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label for="pay_notes"
                                            class="block text-xs font-medium text-gray-900 dark:text-white">Payment
                                            Notes</label>
                                        <textarea type="text" name="pay_notes" id="pay_notes"
                                            class="bg-gray-50 border border-gray-300 text-gray-900 text-xs rounded-lg focus:ring-gray-600 focus:border-gray-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-gray-500 dark:focus:border-gray-500"
                                            placeholder="e.g. Partial payment, etc." required></textarea>
                                    </div>
                                </div>

                                <button type="submit"
                                    class="mt-2 text-white inline-flex items-center bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-md text-xs px-4 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">
                                    {{-- <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"></path></svg> --}}
                                    Pay Invoice
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End payment modal -->

            <script>
                function clearModalFields() {
                    // Clear all form fields
                    const form = document.querySelector('form');
                    form.reset();

                    // Remove any success messages after a delay
                    setTimeout(() => {
                        const successMessage = document.querySelector('[data-success]');
                        if (successMessage) {
                            successMessage.remove();
                        }
                    }, 3000);
                }

                function editInvoice(button) {
                    const id = button.getAttribute('data-id');
                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_company_name').value = button.getAttribute('data-company_name');
                    document.getElementById('edit_invoice_number').value = button.getAttribute('data-invoice_number');
                    document.getElementById('edit_invoice_date').value = button.getAttribute('data-invoice_date');
                    document.getElementById('edit_description').value = button.getAttribute('data-description');
                    document.getElementById('edit_amount').value = button.getAttribute('data-amount');
                    document.getElementById('edit_due_date').value = button.getAttribute('data-due_date');
                    document.getElementById('edit_payment_terms').value = button.getAttribute('data-payment_terms');
                    document.getElementById('edit_billing_cycle').value = button.getAttribute('data-billing_cycle');
                    document.getElementById('edit_payment_method').value = button.getAttribute('data-payment_method');


                    // Set form action
                    const form = document.getElementById('editForm');
                    form.action = `/billing/${id}`;
                }

                function payInvoice(button) {
                    const id = button.getAttribute('data-id');
                    document.getElementById('pay_invoice_number').textContent = button.getAttribute('data-invoice_number');
                    document.getElementById('pay_company_name').textContent = button.getAttribute('data-company_name');
                    document.getElementById('pay_total_amount').textContent = parseFloat(button.getAttribute('data-amount'))
                        .toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'PHP'
                        });
                    document.getElementById('pay_already_paid').textContent = parseFloat(button.getAttribute('data-payment'))
                        .toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'PHP'
                        });
                    const balance = button.getAttribute('data-balance');
                    document.getElementById('pay_outstanding_balance').textContent = parseFloat(balance).toLocaleString('en-US', {
                        style: 'currency',
                        currency: 'PHP'
                    });
                    document.getElementById('pay_amount').setAttribute('max', balance);
                    document.getElementById('pay_amount').value = balance > 0 ? balance : 0;
                    document.getElementById('pay_payment_method').value = button.getAttribute('data-payment_method');

                    document.getElementById('pay_date').setAttribute('min', button.getAttribute('data-invoice_date'));

                    // Set form action
                    const form = document.getElementById('paymentForm');
                    form.action = `/billing/${id}/pay`;
                }
            </script>
        @endsection
