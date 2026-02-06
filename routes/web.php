<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ResumeController;

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

    Route::post('resume/upload-match', [ResumeController::class, 'upload'])->name('resume.upload.match');
    Route::resource('matching', ResumeController::class)->except(['destroy']);

});

require __DIR__ . '/auth.php';
