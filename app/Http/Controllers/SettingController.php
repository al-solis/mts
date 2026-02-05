<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\setting;

class SettingController extends Controller
{
    public function index()
    {
        $settings = setting::first();

        return view('setting.index', compact('settings'));
    }

    public function update(Request $request, Setting $setting)
    {

        $validatedData = $request->validate([
            'education' => 'required|numeric|min:0|max:100',
            'years_of_experience' => 'required|numeric|min:0|max:100',
            'work_experience_relevance' => 'required|numeric|min:0|max:100',
            'skills_match' => 'required|numeric|min:0|max:100',
            'related_certifications' => 'required|numeric|min:0|max:100',
            'general_qualifications' => 'required|numeric|min:0|max:100',
            'ai_matching_criteria' => 'required|numeric|min:0|max:100',
        ]);

        $setting->update([
            'education' => $validatedData['education'],
            'years_of_experience' => $validatedData['years_of_experience'],
            'work_experience_relevance' => $validatedData['work_experience_relevance'],
            'skills' => $validatedData['skills_match'],
            'certifications' => $validatedData['related_certifications'],
            'general' => $validatedData['general_qualifications'],
            'minimum_match_percentage' => $validatedData['ai_matching_criteria'],
            'updated_by' => Auth::id(),
            'updated_at' => now(),
        ]);

        return redirect()->route('setting.index')->with('success', 'Settings updated successfully.');
    }
}
