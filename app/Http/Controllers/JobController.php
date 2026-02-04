<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\job;
use App\Models\company;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $searchloc = $request->input('searchloc');

        $totalJobs = Job::count();
        $inactiveJobs = Job::where('status', '0')->count();
        $activeJobs = Job::where('status', '1')->count();
        $pausedJobs = Job::where('status', '2')->count();
        $closedJobs = Job::where('status', '3')->count();
        $cancelledJobs = Job::where('status', '4')->count();

        $companies = Company::where('status', '1')
            ->orderBy('name')->get();

        $query = Job::with('company');

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%')
                ->orWhere('qualification', 'like', '%' . $search . '%')
                ->orWhere('skill', 'like', '%' . $search . '%')
                ->orWhereHas('company', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($searchloc) {
            $query->whereHas('company', function ($q) use ($searchloc) {
                $q->where('id', $searchloc);
            });
        }

        $jobs = $query->paginate(config('app.pagination'))
            ->appends(['search' => $search, 'status' => $status, 'searchloc' => $searchloc]);

        return view('job.index', compact('jobs', 'companies', 'totalJobs', 'activeJobs', 'inactiveJobs', 'pausedJobs', 'closedJobs', 'cancelledJobs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'qualification' => 'required|string',
            'skill' => 'required|string',
            'salary' => 'required|string|max:100',
            'status' => 'required|in:0,1,2,3,4',
        ]);

        Job::create([
            'company_id' => $request->input('company_id'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'qualification' => $request->input('qualification'),
            'skill' => $request->input('skill'),
            'salary_range' => $request->input('salary'),
            'status' => $request->input('status'),
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return redirect()->route('job.index')->with('success', 'Job posting created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'original_company_id' => 'required|exists:companies,id',
            'edit_title' => 'required|string|max:255',
            'edit_description' => 'required|string',
            'edit_qualification' => 'required|string',
            'edit_skill' => 'required|string',
            'edit_salary' => 'required|string|max:100',
            'edit_status' => 'required|in:0,1,2,3,4',
        ]);

        $job = Job::findOrFail($id);
        $job->update([
            'company_id' => $request->input('original_company_id'),
            'title' => $request->input('edit_title'),
            'description' => $request->input('edit_description'),
            'qualification' => $request->input('edit_qualification'),
            'skill' => $request->input('edit_skill'),
            'salary_range' => $request->input('edit_salary'),
            'status' => $request->input('edit_status'),
            'updated_by' => auth()->id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('job.index')->with('success', 'Job posting updated successfully.');
    }

    public function getJobCount($companyId)
    {
        $jobCount = Job::where('company_id', $companyId)
            ->whereIn('status', ['1', '2'])
            ->count();
        return response()->json(['jobCount' => $jobCount]);
    }
}
