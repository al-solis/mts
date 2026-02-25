<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Deployment;
use App\Models\Resume;
use App\Models\Job;
use App\Models\Company;

class DeploymentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $searchloc = $request->input('searchloc');
        $status = $request->input('status');

        $totalDeployments = Deployment::where('status', '!=', 2)->count();

        $companiesServed = Company::whereHas('jobs.deployments', function ($q) {
            $q->where('deployments.status', 1);
        })
            ->distinct()
            ->count('companies.id');

        $availableForDeployment = Resume::where('tag', 2)
            ->whereDoesntHave('deployments', function ($q) {
                $q->where('status', '!=', 2);
            })
            ->count();

        $activePlacements = Deployment::where('status', 1)->count();

        $companies = Company::all();

        $passedApplicants = Resume::where('tag', 2)
            ->whereDoesntHave('deployments', function ($q) {
                $q->where('status', '!=', 2);
            })
            ->get();

        $query = Deployment::with(['resume.job.company']);

        if ($search) {
            $query->whereHas('resume', function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%");
            });
        }

        if ($searchloc) {
            $query->whereHas('resume.job.company', function ($q) use ($searchloc) {
                $q->where('id', $searchloc);
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $deployments = $query->paginate(10)->withQueryString();

        return view(
            'deployment.index',
            compact(
                'deployments',
                'totalDeployments',
                'companiesServed',
                'availableForDeployment',
                'activePlacements',
                'companies',
                'passedApplicants'
            )
        );
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'applicant_id' => 'required|exists:resumes,id',
            'salary' => 'required|decimal:0,2',
            'agency_fee' => 'required|decimal:0,2',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $resume = Resume::findOrFail($request->applicant_id);

        Deployment::create([
            'resume_id' => $request->applicant_id,
            'salary' => $request->salary,
            'agency_fee' => $request->agency_fee,
            'notes' => $request->notes,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 1, // Active
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('deployment.index')->with('success', 'Deployment created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'edit_salary' => 'required|decimal:0,2',
            'edit_agency_fee' => 'required|decimal:0,2',
            'edit_start_date' => 'nullable|date',
            'edit_end_date' => 'nullable|date|after_or_equal:edit_start_date',
        ]);

        $deployment = Deployment::findOrFail($id);

        $deployment->update([
            'salary' => $request->edit_salary,
            'agency_fee' => $request->edit_agency_fee,
            'notes' => $request->edit_notes,
            'start_date' => $request->edit_start_date,
            'end_date' => $request->edit_end_date,
            'status' => $request->edit_status ?? 0,
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('deployment.index')->with('success', 'Deployment updated successfully.');
    }
}
