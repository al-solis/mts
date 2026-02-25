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
        $search = $request->input('search');
        $status = $request->input('status');
        $searchloc = $request->input('searchloc');

        $companies = Company::all();
        $query = Appointment::with('resume')->with('resume.job')->with('resume.job.company');

        if ($search) {
            $query->whereHas('resume', function ($q) use ($search) {
                $q->where('applicant_name', 'like', '%' . $search . '%');
            })->orWhere('notes', 'like', '%' . $search . '%');
        }

        if ($searchloc) {
            $query->whereHas('resume.job.company', function ($q) use ($searchloc) {
                $q->where('id', $searchloc);
            });
        }

        if ($status !== null) {
            $query->where('status', $status);
        }

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
            'interview_round' => 1,
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

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'applicant_id' => 'required|exists:resumes,id',
            'meeting_type' => 'required|in:1,2',
            'interview_round' => 'required|integer|min:1',
            'interview_date' => 'required|date|after_or_equal:today',
            'interview_time' => 'required',
            'meeting_link' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        $appointment = appointment::create([
            'resume_id' => $request->input('applicant_id'),
            'meeting_type' => $request->input('meeting_type'),
            'interview_round' => $request->input('interview_round'),
            'interview_date' => $request->input('interview_date'),
            'interview_time' => $request->input('interview_time'),
            'meeting_link' => $request->input('meeting_link'),
            'notes' => $request->input('notes'),
            'status' => 0, // Default to upcoming
            'created_by' => Auth::id(),
            'created_at' => now(),
        ]);

        // Update resume tag to indicate interview scheduled
        Resume::where('id', $request->input('resume_id'))->update(['tag' => 1]);

        return redirect()->route('appointment.index')->with('success', 'Appointment scheduled successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'edit_meeting_type' => 'required|in:1,2',
            'edit_interview_round' => 'required|integer|min:1',
            'edit_interview_date' => 'required|date|after_or_equal:today',
            'edit_interview_time' => 'required',
            'edit_meeting_link' => 'nullable|url',
            'edit_notes' => 'nullable|string'
        ]);

        $appointment = appointment::findOrFail($id);
        $appointment->update([
            'meeting_type' => $request->input('edit_meeting_type'),
            'interview_round' => $request->input('edit_interview_round'),
            'interview_date' => $request->input('edit_interview_date'),
            'interview_time' => $request->input('edit_interview_time'),
            'meeting_link' => $request->input('edit_meeting_link'),
            'notes' => $request->input('edit_notes'),
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        // Update resume tag based on appointment status
        // if ($request->input('status') == 0) {
        //     Resume::where('id', $request->input('applicant_id'))->update(['tag' => 1]); // Interview Scheduled
        // } elseif ($request->input('status') == 1) {
        //     Resume::where('id', $request->input('applicant_id'))->update(['tag' => 2]); // Interview Completed
        // } elseif ($request->input('status') == 2) {
        //     Resume::where('id', $request->input('applicant_id'))->update(['tag' => 3]); // Interview Cancelled
        // }

        return redirect()->route('appointment.index')->with('success', 'Appointment updated successfully');
    }

    public function markAsComplete($id)
    {
        $appointment = appointment::findOrFail($id);
        $appointment->update([
            'status' => 1, // Mark as completed
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        // Update resume tag to indicate interview completed
        // Resume::where('id', $appointment->resume_id)->update(['tag' => 2]); // 2 = Interview Completed

        return response()->json(['success' => true, 'message' => 'Appointment marked as complete']);
    }

    public function markAsFailed($id)
    {
        $appointment = appointment::findOrFail($id);
        $appointment->update([
            'tag' => 2, // Mark as failed
            'status' => 1, // Mark as completed
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        // Update resume tag to indicate interview failed
        Resume::where('id', $appointment->resume_id)->update(['tag' => 4]); // 4 = Interview Failed

        return response()->json(['success' => true, 'message' => 'Appointment marked as failed']);
    }

    public function markAsPassed($id)
    {
        $appointment = appointment::findOrFail($id);
        $appointment->update([
            'tag' => 1, // Mark as passed
            'status' => 1, // Mark appointment as completed
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        // Update resume tag to indicate interview passed
        Resume::where('id', $appointment->resume_id)->update(['tag' => 2]); // 2 = Interview Passed

        return response()->json(['success' => true, 'message' => 'Appointment marked as passed']);
    }

    public function scheduleNextRound($id)
    {
        $appointment = appointment::findOrFail($id);
        $appointment->update([
            'interview_round' => $appointment->interview_round + 1,
            'interview_date' => now()->addDays(3),
            'interview_time' => now()->addDays(3)->format('H:i'),
            'status' => 0, // Reset status to upcoming
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('appointment.index')->with('success', 'Next round scheduled successfully');
    }
}
