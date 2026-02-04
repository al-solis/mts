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

        if ($status) {
            $query->where('status', $status);
        }

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
            'contact_email' => 'required|email|max:60',
            'location' => 'required|string|max:150',
            'status' => 'required|in:0,1',
        ]);

        Company::create([
            'name' => $request->input('name'),
            'industry' => $request->input('industry'),
            'contact_person' => $request->input('contact_person'),
            'contact_email' => $request->input('contact_email'),
            'location' => $request->input('location'),
            'status' => $request->input('status'),
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        return redirect()->route('company.index')->with('success', 'Company created successfully.');
    }
}
