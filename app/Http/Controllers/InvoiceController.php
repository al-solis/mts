<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\invoice as Invoice;
use App\Models\company as Company;
use App\Models\User;
use App\Models\resume as Resume;
use App\Models\deployment as Deployment;
use App\Models\payment as Payment;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $searchCompany = $request->input('searchCompany');
        $status = $request->input('status');

        $totalBilled = Invoice::where('status', '!=', 3)->sum('amount');
        $totalCollected = Invoice::where('status', '!=', 3)->sum('payment');
        $outstandingBalance = $totalBilled - $totalCollected;
        $overdueInvoices = Invoice::where('due_date', '<', now())->where('status', '!=', 2)->count();

        $companies = Company::all();
        $deploymentStats = DB::table('deployments as a')
            ->join('resumes as b', 'a.resume_id', '=', 'b.id')
            ->join('job_postings as c', 'b.job_posting_id', '=', 'c.id')
            ->select(
                'c.company_id',
                DB::raw('count(*) as active_deployment'),
                DB::raw('sum(a.agency_fee) as total_agency_fee')
            )
            ->where('a.status', 1)
            ->groupBy('c.company_id')
            ->get()
            ->keyBy('company_id');

        $companies = Company::all();

        foreach ($companies as $company) {
            $company->active_deployments_count = $deploymentStats[$company->id]->active_deployment ?? 0;
            $company->total_agency_fee = $deploymentStats[$company->id]->total_agency_fee ?? 0;
            $company->total_billed = Invoice::where('company_id', $company->id)
                ->where('status', '!=', 3)
                ->sum('amount');
            $company->total_collected = Invoice::where('company_id', $company->id)
                ->where('status', '!=', 3)
                ->sum('payment');
            $company->balance = $company->total_billed - $company->total_collected;
        }

        $query = Invoice::with('company');
        $query2 = Payment::with('invoice');

        if ($searchCompany) {
            $query->where('company_id', $searchCompany);
            $query2->whereHas('invoice', function ($q) use ($searchCompany) {
                $q->where('company_id', $searchCompany);
            });
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('invoice_number', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($status !== null) {
            if ($status == 1) {
                $query->whereIn('status', [1, 2]);
                $query2->where('status', 1);
            } else if ($status == 2) {
                $query->where('status', 3);
                $query2->where('status', 2);

            } else {
                $query->where('status', $status);
                $query2->whereHas('invoice', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            }
        }

        if ($searchCompany) {
            $query->where('company_id', $searchCompany);
            $query2->whereHas('invoice', function ($q) use ($searchCompany) {
                $q->where('company_id', $searchCompany);
            });
        }

        $invoices = $query->get();
        $payments = $query2->get();

        return view(
            'billing.index',
            compact('invoices', 'payments', 'companies', 'totalBilled', 'totalCollected', 'outstandingBalance', 'overdueInvoices')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
        ]);

        $year = now()->year;
        $count = Invoice::whereYear('created_at', $year)->count() + 1;
        $invoiceNumber = 'INV-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $request->invoice_date,
            'company_id' => $request->company_id,
            'description' => $request->description,
            'amount' => $request->amount,
            'payment' => 0,
            'due_date' => $request->due_date,
            'payment_terms' => $request->payment_terms,
            'billing_cycle' => $request->billing_cycle,
            'payment_method' => $request->payment_method,
            'status' => 0, // Unpaid
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->route('billing.index')->with('success', 'Invoice created successfully.');
    }

    public function update(Request $request, $invoice)
    {
        // dd($request->all());
        $request->validate([
            'edit_amount' => 'required|numeric|min:0',
            'edit_description' => 'required|string|max:255',
        ]);

        $invoice = Invoice::findOrFail($invoice);

        $paymentSum = Payment::where('invoice_id', $invoice->id)
            ->where('status', 1)
            ->sum('amount');

        //Check if invoice has payments, if yes, prevent changing company and amount

        if ($invoice->payment < $paymentSum) {
            return redirect()->route('billing.index')->with('error', 'Cannot change amount for an invoice. Payments is greater than invoice amount.');
        }

        if ($request->edit_amount < $paymentSum) {
            return redirect()->route('billing.index')->with('error', 'Cannot set invoice amount less than total payments. Payments is greater than invoice amount.');
        }

        $invoice->update([
            'invoice_date' => $request->edit_invoice_date,
            'description' => $request->edit_description,
            'amount' => $request->edit_amount,
            'due_date' => $request->edit_due_date,
            'payment_terms' => $request->edit_payment_terms,
            'billing_cycle' => $request->edit_billing_cycle,
            'payment_method' => $request->edit_payment_method,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        if ($invoice->payment < $invoice->amount) {
            $invoice->update([
                'status' => 1, // Partially Paid
            ]);
        } else {
            $invoice->update([
                'status' => 2, // Paid
            ]);
        }

        return redirect()->route('billing.index')->with('success', 'Invoice updated successfully.');
    }

    public function payInvoice(Request $request, $invoice)
    {
        $request->validate([
            'pay_amount' => 'required|numeric|min:0',
            'pay_reference' => 'nullable|string|max:255',
            'pay_date' => 'required|date',
            'pay_payment_method' => 'required|string|max:255',
        ]);

        $invoice = Invoice::findOrFail($invoice);

        $paymentSum = Payment::where('invoice_id', $invoice->id)
            ->where('status', 1)
            ->sum('amount');

        $balance = $invoice->amount - $paymentSum;

        if ($request->pay_amount > $balance) {
            return redirect()->route('billing.index')->with('error', 'Payment amount cannot be greater than outstanding balance.');
        }

        $prefix = 'PYMT';
        $year = now()->year;
        $count = Payment::whereYear('created_at', $year)->count() + 1;
        $paymentReference = $prefix . '-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);

        Payment::create([
            'payment_number' => $paymentReference,
            'invoice_id' => $invoice->id,
            'amount' => $request->pay_amount,
            'payment_date' => $request->pay_date,
            'payment_method' => $request->pay_payment_method,
            'reference' => $request->pay_reference,
            'notes' => $request->pay_notes,
            'status' => 1, // Paid
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        // Update invoice payment sum
        $totalPayment = Payment::where('invoice_id', $invoice->id)
            ->where('status', 1)
            ->sum('amount');

        $invoice->update([
            'payment' => $totalPayment,
            'status' => $totalPayment >= $invoice->amount ? 2 : 1, // Paid or Partially Paid
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('billing.index')->with('success', 'Payment recorded successfully.');
    }

    public function voidInvoice(Request $request, $invoice)
    {
        $invoice = Invoice::findOrFail($invoice);

        $paymentSum = Payment::where('invoice_id', $invoice->id)
            ->where('status', 1)
            ->sum('amount');

        if ($paymentSum > 0) {
            return redirect()->route('billing.index')->with('error', 'Cannot void invoice with payments. Please void the payment(s) associated with this invoice first before voiding the invoice.');
        }

        $invoice->update([
            'status' => 3, // Voided
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('billing.index')->with('success', 'Invoice voided successfully.');
    }

    public function voidPayment(Request $request, $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        $payment->update([
            'status' => 2,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        $totalPayment = Payment::where('invoice_id', $payment->invoice_id)
            ->where('status', 1)
            ->sum('amount');

        //update invoice
        DB::table('invoices')
            ->where('id', $payment->invoice_id)
            ->update([
                'payment' => $totalPayment,
                'status' => $totalPayment >= $payment->invoice->amount ? 2 : ($totalPayment == 0 ? 0 : 1),
            ]);

        return redirect()->route('billing.index')->with('success', 'Payment voided successfully.');
    }

    public function updatePayment(Request $request, $paymentId)
    {
        $request->validate([
            'edit_pay_amount' => 'required|numeric|min:0',
            'edit_pay_reference' => 'nullable|string|max:255',
            'edit_pay_date' => 'required|date',
            'edit_pay_payment_method' => 'required|string|max:255',
        ]);

        $payment = Payment::findOrFail($paymentId);
        $invoice = Invoice::findOrFail($payment->invoice_id);

        $paymentSumExcludingCurrent = Payment::where('invoice_id', $invoice->id)
            ->where('status', 1)
            ->where('id', '!=', $payment->id)
            ->sum('amount');

        $balance = $invoice->amount - $paymentSumExcludingCurrent;

        if ($request->edit_pay_amount > $balance) {
            return redirect()->route('billing.index')->with('error', 'Payment amount cannot be greater than outstanding balance.');
        }

        $payment->update([
            'amount' => $request->edit_pay_amount,
            'payment_date' => $request->edit_pay_date,
            'payment_method' => $request->edit_pay_payment_method,
            'reference' => $request->edit_pay_reference,
            'notes' => $request->edit_pay_notes,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        $totalPayment = Payment::where('invoice_id', $invoice->id)
            ->where('status', 1)
            ->sum('amount');

        // Update invoice payment sum
        $invoice->update([
            'payment' => $totalPayment,
            'status' => $totalPayment >= $invoice->amount ? 2 : 1, // Paid or Partially Paid
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('billing.index')->with('success', 'Payment updated successfully.');
    }

    public function getBillingInfo($companyId)
    {
        $company = Company::findOrFail($companyId);

        $totalBilled = Invoice::where('company_id', $companyId)->where('status', '!=', 3)->sum('amount');
        $totalCollected = Invoice::where('company_id', $companyId)->where('status', '!=', 3)->sum('payment');
        $outstandingBalance = $totalBilled - $totalCollected;
        $overdueInvoices = Invoice::where('company_id', $companyId)->where('due_date', '<', now())->where('status', '!=', 2)->count();

        $totalDuefromDeployments = DB::table('deployments as a')
            ->join('resumes as b', 'a.resume_id', '=', 'b.id')
            ->join('job_postings as c', 'b.job_posting_id', '=', 'c.id')
            ->where('c.company_id', $companyId)
            ->where('a.status', 1)
            ->sum('a.agency_fee');

        return response()->json([
            'total_due_from_deployments' => $totalDuefromDeployments,
            'total_billed' => $totalBilled,
            'total_collected' => $totalCollected,
            'outstanding_balance' => $outstandingBalance,
            'overdue_invoices' => $overdueInvoices,
        ]);
    }
}
