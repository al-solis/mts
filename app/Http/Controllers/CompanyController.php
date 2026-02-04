<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $totalCompanies = Company::count();
        $activeCompanies = Company::where('status', 'active')->count();
        $inactiveCompanies = Company::where('status', 'inactive')->count();

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
}
