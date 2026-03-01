<!DOCTYPE html>
<html>
@php
    use Carbon\Carbon;
@endphp

<head>
    <meta charset="utf-8">
    <title>Invoice Report</title>

    <style>
        body {
            /* font-family: DejaVu Sans, sans-serif;  */
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }

        .sub-title {
            font-size: 12px;
            color: #555;
        }

        .section {
            margin-top: 20px;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th,
        table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }

        table th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 40px;
        }

        .page-number {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: left;
            font-size: 8px;
            color: #666;
            padding: 5px 0;
            border-top: 1px solid #ddd;
        }

        .signature {
            margin-top: 50px;
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .status-returned {
            color: green;
            font-weight: bold;
        }

        .status-damaged {
            color: orange;
            font-weight: bold;
        }

        .status-lost {
            color: red;
            font-weight: bold;
        }

        .status-pending {
            color: gray;
            font-weight: bold;
        }
    </style>
</head>

<body>

    {{-- HEADER --}}
    <div class="header">
        <div class="title">{{ env('APP_COMPANY_NAME') }}</div>
        <div class="sub-title">{{ env('APP_COMPANY_ADDRESS') }}</div>
        <div class="sub-title">{{ env('APP_COMPANY_CONTACT') }}</div>
        <br>
        <br>
        <div class="sub-title" style="font-weight: bolder; font-size: 15px">INVOICE REPORT</div>
        <div class="sub-title">Range: {{ $pDateRange != 'custom' ? $pDateRange : $pFromDate . ' to ' . $pToDate }}</div>
        <div class="sub-title">Company: {{ $pCompanyName }}</div>
        <div class="sub-title">Status: {{ $statusLabel }}</div>
    </div>

    {{-- DETAILS --}}
    <div class="section">
        {{-- <div class="section-title">Report Details</div> --}}
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Due</th>
                    <th>Invoice No</th>
                    <th>Description</th>
                    <th>Company</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Balance</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotalAmount = 0;
                    $grandTotalPayment = 0;
                @endphp

                @foreach ($invoices as $invoice)
                    @php
                        $grandTotalAmount += $invoice->status == 3 ? 0 : $invoice->amount;
                        $grandTotalPayment += $invoice->payment;

                        $statusLable = [
                            '0' => 'Unpaid',
                            '1' => 'Partial Payment',
                            '2' => 'Paid',
                            '3' => 'Void',
                            '4' => 'Overdue',
                        ];
                    @endphp
                    <tr>
                        <td>{{ Carbon::parse($invoice->invoice_date)->format('Y-m-d') }}</td>
                        <td>{{ Carbon::parse($invoice->due_date)->format('Y-m-d') }}</td>
                        <td>{{ $invoice->invoice_number }}</td>
                        <td>{{ $invoice->description ?? '' }}</td>
                        <td>{{ $invoice->company->name ?? '' }}</td>
                        <td class="text-right">
                            {{ $invoice->status == 3 ? number_format($invoice->amount * -1, 2) : number_format($invoice->amount, 2) }}
                        </td>
                        <td class="text-right">{{ number_format($invoice->payment, 2) }}</td>
                        <td class="text-right">
                            {{ $invoice->status == 3 ? '0.00' : number_format($invoice->amount - $invoice->payment, 2) }}
                        </td>
                        <td>{{ $statusLable[$invoice->status] ?? 'Unknown' }}</td>
                    </tr>
                @endforeach

                @if ($invoices->isEmpty())
                    <tr>
                        <td colspan="9" style="text-align: center; font-size: 11px; color: #555">No invoices found.
                        </td>
                    </tr>
                @endif
                <tr>
                    <td colspan="5" class="text-right"><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalAmount, 2) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalPayment, 2) }}</strong></td>
                    <td class="text-right">
                        <strong>{{ number_format($grandTotalAmount - $grandTotalPayment, 2) }}</strong>
                    </td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <p style="text-align: center; font-size: 8px; color:#555"><i>***Nothing Follows***</i></p>

        <div class="page-number">
            Generated on {{ now()->format('F d, Y') }}
        </div>
</body>

</html>
