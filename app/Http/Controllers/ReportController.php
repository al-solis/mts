<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\payment;
use App\Models\JobPosting;
use App\Models\Resume;

class ReportController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('reports.index', compact('companies'));
    }

    public function generateInvoiceReport(Request $request)
    {
        $companyId = $request->input('company_id');
        $pCompanyName = Company::find($companyId)->name ?? 'All Companies';
        $pFromDate = $request->input('from_date');
        $pToDate = $request->input('to_date');
        $pDateRange = $request->input('date_range') ?? 'this_month';
        $status = $request->input('status');
        $query = Invoice::query()->with('company');

        $dateRangeLabels = [
            'this_month' => Carbon::now()->format('F Y'),
            'last_month' => Carbon::now()->subMonth()->format('F Y'),
            'this_quarter' => Carbon::now()->startOfQuarter()->format('F Y') . ' - ' . Carbon::now()->endOfQuarter()->format('F Y'),
            'this_year' => Carbon::now()->format('Y'),
            'custom' => 'custom',
        ];
        $pDateRange = $dateRangeLabels[$pDateRange] ?? 'Custom Range';

        $statusLabels = [
            '0' => 'Unpaid',
            '1' => 'Partial Payment',
            '2' => 'Paid',
            '3' => 'Voided',
            '4' => 'Overdue'
        ];

        $statusLabel = $statusLabels[$status] ?? 'All Statuses';

        switch ($pDateRange) {
            case 'this_month':
                $query->whereMonth('due_date', Carbon::now()->month)
                    ->whereYear('due_date', Carbon::now()->year);
                break;

            case 'last_month':
                $query->whereMonth('due_date', Carbon::now()->subMonth()->month)
                    ->whereYear('due_date', Carbon::now()->subMonth()->year);
                break;

            case 'this_quarter':
                $query->whereBetween('due_date', [
                    Carbon::now()->startOfQuarter()->format('Y-m-d'),
                    Carbon::now()->endOfQuarter()->format('Y-m-d')
                ]);
                break;

            case 'this_year':
                $query->whereYear('due_date', Carbon::now()->year);
                break;

            case 'custom':
                if ($pFromDate && $pToDate) {
                    $query->whereBetween('due_date', [$pFromDate, $pToDate]);
                }
                break;
        }

        if ($companyId !== null) {
            $query->where('company_id', $companyId);
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $invoices = $query
            ->orderBy('due_date', 'desc')
            ->orderBy('invoice_date', 'desc')
            ->get();

        $pdf = Pdf::loadView(
            'reports.invoice',
            compact('invoices', 'pDateRange', 'status', 'pFromDate', 'pToDate', 'pCompanyName', 'statusLabel')
        )
            ->setPaper('letter', 'landscape');

        return $pdf->stream('invoice_report.pdf');
    }

    public function generatePaymentReport(Request $request)
    {
        $companyId = $request->input('company_id');
        $pCompanyName = Company::find($companyId)->name ?? 'All Companies';
        $pFromDate = $request->input('from_date');
        $pToDate = $request->input('to_date');
        $pDateRange = $request->input('date_range') ?? 'this_month';
        $status = $request->input('status');
        $method = $request->input('method');
        $query = Payment::query()->with('invoice.company');

        $dateRangeLabels = [
            'this_month' => Carbon::now()->format('F Y'),
            'last_month' => Carbon::now()->subMonth()->format('F Y'),
            'this_quarter' => Carbon::now()->startOfQuarter()->format('F Y') . ' - ' . Carbon::now()->endOfQuarter()->format('F Y'),
            'this_year' => Carbon::now()->format('Y'),
            'custom' => 'custom',
        ];
        $pDateRange = $dateRangeLabels[$pDateRange] ?? 'Custom Range';

        $statusLabels = [
            '1' => 'Active',
            '2' => 'Voided',
        ];
        $statusLabel = $statusLabels[$status] ?? 'All Statuses';

        $methodLabels = [
            '1' => 'Bank Transfer',
            '2' => 'Credit Card',
            '3' => 'Cash',
            '4' => 'Check',
            '5' => 'Online Payment',
        ];

        $pmethodLabels = $methodLabels[$method] ?? 'All Methods';

        switch ($pDateRange) {
            case 'this_month':
                $query->whereMonth('payment_date', Carbon::now()->month)
                    ->whereYear('payment_date', Carbon::now()->year);
                break;

            case 'last_month':
                $query->whereMonth('payment_date', Carbon::now()->subMonth()->month)
                    ->whereYear('payment_date', Carbon::now()->subMonth()->year);
                break;

            case 'this_quarter':
                $query->whereBetween('payment_date', [
                    Carbon::now()->startOfQuarter()->format('Y-m-d'),
                    Carbon::now()->endOfQuarter()->format('Y-m-d')
                ]);
                break;

            case 'this_year':
                $query->whereYear('payment_date', Carbon::now()->year);
                break;

            case 'custom':
                if ($pFromDate && $pToDate) {
                    $query->whereBetween('payment_date', [$pFromDate, $pToDate]);
                }
                break;
        }

        if ($method !== null) {
            $query->where('payment_method', $method);
        }

        if ($companyId !== null) {
            $query->whereHas('invoice', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $payments = $query
            ->orderBy('payment_date', 'desc')
            ->get();

        $pdf = Pdf::loadView(
            'reports.payment',
            compact('payments', 'pDateRange', 'status', 'pFromDate', 'pToDate', 'pCompanyName', 'statusLabel', 'pmethodLabels')
        )
            ->setPaper('letter', 'landscape');

        return $pdf->stream('payment_report.pdf');

    }
}