<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/company/{company}/jobs/count', [JobController::class, 'getJobCount']);
    Route::resource('company', CompanyController::class)->except(['destroy']);

    Route::resource('job', JobController::class)->except(['destroy']);

    Route::resource('setting', SettingController::class)->except(['destroy']);

    Route::get('resume/by-job/{jobId}', [ResumeController::class, 'getByJob'])->name('resume.by.job');
    Route::post('resume/upload-match', [ResumeController::class, 'upload'])->name('resume.upload.match');
    Route::resource('matching', ResumeController::class)->except(['destroy']);

    Route::resource('appointment', AppointmentController::class)->except(['destroy']);

    Route::post('/matching/schedule/{id}', [AppointmentController::class, 'scheduleAppointment'])->name('matching.schedule');
    Route::post('/matching/pass/{id}', [ResumeController::class, 'markAsPassed'])->name('matching.pass');

    Route::get('/api/companies/{companyId}/jobs', [JobController::class, 'getJobsByCompany']);
    Route::get('/api/jobs/{jobId}/applicants', [ResumeController::class, 'getApplicantsByJob']);
    Route::post('/appointment/{id}/complete', [AppointmentController::class, 'markAsComplete'])->name('appointment.complete');
    Route::post('/appointment/{id}/fail', [AppointmentController::class, 'markAsFailed'])->name('appointment.fail');
    Route::post('/appointment/{id}/pass', [AppointmentController::class, 'markAsPassed'])->name('appointment.pass');
    Route::get('/appointment/{id}/schedule-next-round', [AppointmentController::class, 'scheduleNextRound']);

    Route::resource('deployment', DeploymentController::class)->except(['destroy']);

    Route::resource('billing', InvoiceController::class)->except(['destroy']);
    Route::resource('payment', PaymentController::class)->except(['destroy']);


});

require __DIR__ . '/auth.php';
