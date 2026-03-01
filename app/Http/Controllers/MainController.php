<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\Deployment;
use App\Models\Resume;
use App\Models\Appointment;
use App\Models\Company;
use App\Models\JobPosting;
use App\Models\Setting;

class MainController extends Controller
{
    public function index()
    {
        $minMatch = Setting::value('minimum_match_percentage') ?? 70;

        $totalApplicants = Resume::count();
        $totalDeployments = Deployment::where('status', 1)->count();
        $totalInterviews = Appointment::count();
        $totalActiveJobs = JobPosting::where('status', 1)->count();
        $totalCompanies = Company::count();
        $totalPlacements = Deployment::where('status', 1)->count();
        $aveMatchScore = Resume::avg('match_percentage');
        $pendingAppointments = Appointment::where('status', 0)->count();
        $qualifiedApplicants = Resume::where('match_percentage', '>=', $minMatch)->count();
        $pendingApplicants = Resume::where('tag', '=', 0)->count();


        $recentActivities = collect();

        // Get recent deployments
        $deployments = Deployment::with(['resume'])
            ->latest()
            ->take(2)
            ->get()
            ->map(function ($d) {
                return $d->resume->applicant_name .
                    ' deployed to ' .
                    ($d->resume->job->company->name ?? 'Unknown Company');
            });

        // Get recent appointments
        $appointments = Appointment::with('resume')
            ->latest()
            ->take(2)
            ->get()
            ->map(function ($a) {
                return 'Appointment scheduled with ' . ($a->resume->applicant_name ?? 'Unknown Applicant');
            });

        // Get recent resumes
        $newResumes = Resume::latest()
            ->take(1)
            ->get()
            ->map(function ($r) {
                return 'Resume uploaded: ' . ($r->applicant_name ?? 'Unknown Applicant');
            });

        $recentActivities = $deployments->concat($appointments)->concat($newResumes)->take(5);

        $topMatches = Resume::orderBy('match_percentage', 'desc')->take(3)->get();

        return view(
            'main',
            compact(
                'totalApplicants',
                'totalDeployments',
                'totalInterviews',
                'totalActiveJobs',
                'totalCompanies',
                'totalPlacements',
                'aveMatchScore',
                'pendingAppointments',
                'qualifiedApplicants',
                'minMatch',
                'pendingApplicants',
                'recentActivities',
                'topMatches'
            )
        );
    }

}
