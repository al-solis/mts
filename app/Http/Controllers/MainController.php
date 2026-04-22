<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\Models\deployment as Deployment;
use App\Models\resume as Resume;
use App\Models\appointment as Appointment;
use App\Models\company as Company;
use App\Models\JobPosting;
use App\Models\setting;

class MainController extends Controller
{
    public function index()
    {
        $minMatch = setting::value('minimum_match_percentage') ?? 70;

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
            ->take(4)
            ->get()
            ->map(function ($d) {
                return ucwords(strtolower($d->resume->applicant_name)) .
                    ' deployed to ' .
                    ($d->resume->job->company->name ?? 'Unknown Company');
            });

        // Get recent appointments
        $appointments = Appointment::with('resume')
            ->latest()
            ->take(3)
            ->get()
            ->map(function ($a) {
                return 'Appointment scheduled with ' . ucwords(strtolower($a->resume->applicant_name)) ?? 'Unknown Applicant';
            });

        // Get recent resumes
        $newResumes = Resume::latest()
            ->take(3)
            ->get()
            ->map(function ($r) {
                return 'Resume uploaded: ' . ucwords(strtolower($r->applicant_name)) ?? 'Unknown Applicant';
            });

        $recentActivities = $deployments->concat($appointments)->concat($newResumes)->take(10);

        $topMatches = Resume::orderBy('match_percentage', 'desc')->take(5)->get();

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
