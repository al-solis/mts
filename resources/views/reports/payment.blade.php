<!DOCTYPE html>
<html>
@php
    use Carbon\Carbon;
@endphp

<head>
    <meta charset="utf-8">
    <title>Payment Listing Report</title>

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
        <div class="sub-title" style="font-weight: bolder; font-size: 15px">PAYMENT LISTING REPORT</div>
        <div class="sub-title">Range: {{ $pDateRange != 'custom' ? $pDateRange : $pFromDate . ' to ' . $pToDate }}</div>
        <div class="sub-title">Company: {{ $pCompanyName }}</div>
        <div class="sub-title">Method: {{ $pmethodLabels }}</div>
        <div class="sub-title">Status: {{ $statusLabel }}</div>
    </div>

    {{-- DETAILS --}}
    <div class="section">
        {{-- <div class="section-title">Report Details</div> --}}
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Payment No</th>
                    <th>Apply To</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th>Company</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotalPayment = 0;
                @endphp

                @foreach ($payments as $payment)
                    @php

                        $grandTotalPayment += $payment->status == 2 ? 0 : $payment->amount;

                        $statusLabels = [
                            '1' => 'Active',
                            '2' => 'Voided',
                        ];

                        $methodLabels = [
                            '1' => 'Bank Transfer',
                            '2' => 'Credit Card',
                            '3' => 'Cash',
                            '4' => 'Check',
                            '5' => 'Online Payment',
                        ];
                    @endphp
                    <tr>
                        <td>{{ Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                        <td>{{ $payment->payment_number }}</td>
                        <td>{{ $payment->invoice->invoice_number ?? '' }}</td>
                        <td>{{ $payment->reference ?? '' }}</td>
                        <td>{{ $payment->notes ?? '' }}</td>
                        <td>{{ $payment->invoice->company->name ?? '' }}</td>
                        <td>{{ $methodLabels[$payment->payment_method] ?? 'Unknown Method' }}</td>
                        <td class="text-right">
                            {{ $payment->status == 2 ? number_format($payment->amount * -1, 2) : number_format($payment->amount, 2) }}
                        </td>
                        <td>{{ $statusLabels[$payment->status] ?? 'Unknown Status' }}</td>
                    </tr>
                @endforeach

                @if ($payments->isEmpty())
                    <tr>
                        <td colspan="9" style="text-align: center; font-size: 11px; color: #555">No payments found.
                        </td>
                    </tr>
                @endif
                <tr>
                    <td colspan="7" class="text-right"><strong>Grand Total</strong></td>
                    <td class="text-right"><strong>{{ number_format($grandTotalPayment, 2) }}</strong></td>
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
