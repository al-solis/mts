<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;


class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $totalCompanies = Company::count();
        $activeCompanies = Company::where('status', '1')->count();
        $inactiveCompanies = Company::where('status', '0')->count();

        $query = Company::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
            $query->orWhere('industry', 'like', '%' . $search . '%');
            $query->orWhere('contact_person', 'like', '%' . $search . '%');
            $query->orWhere('contact_email', 'like', '%' . $search . '%');
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        $query = $query->withCount('jobs')
            ->withCount([
                'jobs as active_jobs_count' => function ($q) {
                    $q->where('status', '1');
                }
            ])
            ->withCount([
                'jobs as successful_placements_count' => function ($q) {
                    $q->where('status', '3'); // Assuming status '3' means successful placement
                }
            ]);

        $companies = $query->paginate(config('app.pagination'))
            ->appends(['search' => $search, 'status' => $status]);

        return view('company.index', compact('companies', 'totalCompanies', 'activeCompanies', 'inactiveCompanies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'industry' => 'required|string|max:150',
            'contact_person' => 'required|string|max:150',
            'email' => 'required|email|max:60',
            'location' => 'required|string|max:150',
            'status' => 'required|in:0,1',
        ]);

        Company::create([
            'name' => $request->input('name'),
            'industry' => $request->input('industry'),
            'contact_person' => $request->input('contact_person'),
            'contact_email' => $request->input('email'),
            'location' => $request->input('location'),
            'status' => $request->input('status'),
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->route('company.index')->with('success', 'Company created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'edit_name' => 'required|string|max:255',
            'edit_industry' => 'required|string|max:150',
            'edit_contact_person' => 'required|string|max:150',
            'edit_email' => 'required|email|max:60',
            'edit_location' => 'required|string|max:150',
            'edit_status' => 'required|in:0,1',
        ]);

        $company = Company::findOrFail($id);
        $company->update([
            'name' => $request->input('edit_name'),
            'industry' => $request->input('edit_industry'),
            'contact_person' => $request->input('edit_contact_person'),
            'contact_email' => $request->input('edit_email'),
            'location' => $request->input('edit_location'),
            'status' => $request->input('edit_status'),
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('company.index')->with('success', 'Company updated successfully.');
    }
}
