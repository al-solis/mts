<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\Company;
use App\Models\User;
use App\Models\Resume;
use App\Models\deployment;
use App\Models\Payment;

class InvoiceController extends Controller
{
    public function index()
    {
        $totalBilled = Invoice::sum('amount');
        $totalCollected = Invoice::sum('payment');
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

        $invoices = Invoice::with('company')->get();

        return view(
            'billing.index',
            compact('invoices', 'companies', 'totalBilled', 'totalCollected', 'outstandingBalance', 'overdueInvoices')
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

        return redirect()->route('billing.index')->with('success', 'Invoice updated successfully.');
    }
}
