<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\Company;
use App\Models\appointment;

class AppointmentController extends Controller
{
    // public function index(Request $request)
    // {
    //     $search = $request->input('search');
    //     $status = $request->input('status');
    //     $searchloc = $request->input('searchloc');

    //     $companies = Company::all();
    //     $totalAppointments = appointment::count();
    //     $upcomingAppointments = appointment::where('status', 0)->count();
    //     $completedAppointments = appointment::where('status', 1)->count();
    //     $cancelledAppointments = appointment::where('status', 2)->count();

    //     $query = appointment::query();
    //     if ($search) {
    //         $query->whereHas('job', function ($q) use ($search) {
    //             $q->where('title', 'like', '%' . $search . '%');
    //         });
    //         $query->orWhereHas('resume', function ($q) use ($search) {
    //             $q->where('applicant_name', 'like', '%' . $search . '%');
    //         });
    //     }
    //     if ($searchloc) {
    //         $query->whereHas('company', function ($q) use ($searchloc) {
    //             $q->where('name', 'like', '%' . $searchloc . '%');
    //         });
    //     }

    //     if ($status !== null) {
    //         $query->where('status', $status);
    //     }

    //     $appointments = $query->get();
    //     return view(
    //         'appointment.index',
    //         compact('appointments', 'totalAppointments', 'upcomingAppointments', 'completedAppointments', 'cancelledAppointments', 'companies')
    //     );
    // }


    public function index(Request $request)
    {
        $companies = Company::all();
        $query = Appointment::with('resume')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('resume', function ($r) use ($request) {
                    $r->where('applicant', 'like', '%' . $request->search . '%')
                        ->orWhere('file_name', 'like', '%' . $request->search . '%');
                })->orWhere('notes', 'like', '%' . $request->search . '%');
            })
            ->when($request->status !== null && $request->status !== '', function ($q) use ($request) {
                $q->where('status', $request->status);
            });

        $appointments = $query->orderBy('interview_date')->orderBy('interview_time')->get();

        return view('appointment.index', [
            'appointments' => $appointments,
            'totalAppointments' => $appointments->count(),
            'upcomingAppointments' => $appointments->where('status', 0)->count(),
            'completedAppointments' => $appointments->where('status', 1)->count(),
            'cancelledAppointments' => $appointments->where('status', 2)->count(),
            'upcoming' => $appointments->where('status', 0),
            'completed' => $appointments->where('status', 1),
            'companies' => $companies,
        ]);
    }

    public function scheduleAppointment($id)
    {
        $resume = Resume::findOrFail($id);
        appointment::create([
            'resume_id' => $resume->id,
            'meeting_type' => '2', // online meeting
            'interview_round' => 'First Round',
            'interview_date' => now()->addDays(3), // Example: Schedule for 3 days later
            'interview_time' => now()->addDays(3), // Example: Schedule for 3 days later
            'meeting_link' => null,
            'notes' => null,
            'status' => 0, // 0 = upcoming
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        $resume->update(['tag' => 1]); // 1 = Interview Scheduled

        return response()->json(['success' => true, 'message' => 'Appointment scheduled successfully']);
    }
}
