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

class MetricsController extends Controller
{
    public function index()
    {
        return view('metrics.index');
    }

    public function data(Request $request)
    {
        $start = Carbon::parse($request->start_date)->startOfDay();
        $end = Carbon::parse($request->end_date)->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | CORE COUNTS
        |--------------------------------------------------------------------------
        */

        $totalDeployments = Deployment::where('status', 1)
            ->whereBetween('start_date', [$start, $end])->count();

        $activeDeployments = Deployment::where('status', 1)
            ->whereBetween('start_date', [$start, $end])
            ->count();

        $totalApplicants = Resume::whereBetween('created_at', [$start, $end])->count();

        $totalInterviews = Appointment::whereBetween('interview_date', [$start, $end])->count();

        $uniqueCompanies = DB::table('deployments as b')
            ->join('resumes as a', 'a.id', '=', 'b.resume_id')
            ->join('job_postings as c', 'a.job_posting_id', '=', 'c.id')
            ->join('companies as d', 'c.company_id', '=', 'd.id')
            ->whereBetween('b.start_date', [$start, $end])
            ->distinct('d.id')
            ->count('d.id');

        $conversionRate = $totalApplicants > 0
            ? round(($totalDeployments / $totalApplicants) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | AVG TIME TO DEPLOY
        |--------------------------------------------------------------------------
        */

        $avgTimeToDeploy = Deployment::whereBetween('deployments.start_date', [$start, $end])
            ->join('resumes', 'deployments.resume_id', '=', 'resumes.id')
            ->avg(DB::raw('DATEDIFF(DAY, resumes.created_at, deployments.start_date)'));


        /*
        |--------------------------------------------------------------------------
        | BILLED VS PAYMENT PER COMPANY (BAR CHART)
        |--------------------------------------------------------------------------
        */
        $billing = DB::table('companies as c')
            ->leftJoin('invoices as i', function ($join) use ($start, $end) {
                $join->on('c.id', '=', 'i.company_id')
                    ->whereBetween('i.invoice_date', [$start, $end])
                    ->where('i.status', '!=', 3);
            })
            ->leftJoin('payments as p', function ($join) use ($start, $end) {
                $join->on('i.id', '=', 'p.invoice_id')
                    ->whereBetween('p.payment_date', [$start, $end])
                    ->where('p.status', 1);
            })
            ->groupBy('c.id', 'c.name')
            ->select(
                'c.name',
                DB::raw('COALESCE(SUM(DISTINCT i.amount),0) as total_billed'),
                DB::raw('COALESCE(SUM(p.amount),0) as total_paid')
            )
            ->orderBy('c.name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DAILY DEPLOYMENTS (LINE CHART)
        |--------------------------------------------------------------------------
        */

        $dailyDeployments = Deployment::where('status', 1)
            ->whereBetween('deployments.start_date', [$start, $end])
            ->selectRaw('CAST(deployments.start_date AS DATE) as deployment_date, COUNT(*) as total')
            ->groupBy(DB::raw('CAST(deployments.start_date AS DATE)'))
            ->orderBy('deployment_date', 'asc')
            ->get();

        $dailyDeployments = $dailyDeployments->map(fn($item) => [
            'date' => Carbon::parse($item->deployment_date)->format('M d'),
            'total' => $item->total
        ]);
        /*
        |--------------------------------------------------------------------------
        | TOP COMPANIES BY DEPLOYMENTS
        |--------------------------------------------------------------------------
        */

        $topCompanies = Company::select(
            'companies.id',
            'companies.name',
            DB::raw('COUNT(deployments.id) as deployments_count')
        )
            ->join('job_postings', 'companies.id', '=', 'job_postings.company_id')
            ->join('resumes', 'job_postings.id', '=', 'resumes.job_posting_id')
            ->join('deployments', 'resumes.id', '=', 'deployments.resume_id')
            ->where('deployments.status', 1)
            ->whereBetween('deployments.start_date', [$start, $end])
            ->groupBy('companies.id', 'companies.name')
            ->orderByDesc('deployments_count')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DEPLOYMENTS BY INDUSTRY (PIE)
        |--------------------------------------------------------------------------
        */

        $industryData = DB::table('deployments as d')
            ->join('resumes as c', 'c.id', '=', 'd.resume_id')
            ->join('job_postings as b', 'b.id', '=', 'c.job_posting_id')
            ->join('companies as a', 'a.id', '=', 'b.company_id')
            ->where('d.status', 1)
            ->whereBetween('d.start_date', [$start, $end])
            ->groupBy('a.industry')
            ->select('a.industry', DB::raw('COUNT(*) as cnt'))
            ->orderByDesc('cnt')
            ->get()
            ->mapWithKeys(fn($item) => [$item->industry => $item->cnt]); // => {"Tech":12, "Finance":8,...}

        /*
        |--------------------------------------------------------------------------
        | MONTHLY DEPLOYMENTS
        |--------------------------------------------------------------------------
        */

        $monthlyDeployments = Deployment::whereBetween('deployments.start_date', [$start, $end])
            ->selectRaw('
        YEAR(deployments.start_date) as year,
        MONTH(deployments.start_date) as month,
        COUNT(*) as total
    ')
            ->groupBy(
                DB::raw('YEAR(deployments.start_date)'),
                DB::raw('MONTH(deployments.start_date)')
            )
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | TOP PERFORMING COMPANIES
        |--------------------------------------------------------------------------
        */

        $topPerformers = Company::with([
            'jobs.resumes.deployments' => function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end]);
            }
        ])->get()->map(function ($company) {

            $applicants = $company->jobs->flatMap->resumes->count();
            $deployments = $company->jobs->flatMap->resumes->flatMap->deployments->count();

            $successRate = $applicants > 0
                ? round(($deployments / $applicants) * 100)
                : 0;

            return [
                'name' => $company->name,
                'applicants' => $applicants,
                'deployments' => $deployments,
                'success' => $successRate
            ];
        })
            ->sortByDesc('success')
            ->take(5)
            ->values();

        return response()->json([
            'summary' => [
                'totalDeployments' => $totalDeployments,
                'activeDeployments' => $activeDeployments,
                'totalApplicants' => $totalApplicants,
                'totalInterviews' => $totalInterviews,
                'uniqueCompanies' => $uniqueCompanies,
                'conversionRate' => $conversionRate,
                'avgTimeToDeploy' => round($avgTimeToDeploy ?? 0, 1),
            ],
            'billing' => $billing,
            'dailyDeployments' => $dailyDeployments,
            'topCompanies' => $topCompanies,
            'industryData' => $industryData,
            'monthlyDeployments' => $monthlyDeployments,
            'topPerformers' => $topPerformers
        ]);
    }
}
