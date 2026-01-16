<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarLessonsController;
use App\Http\Controllers\EnrollmentContractController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth'])
    ->get('/admin/calendar/lessons', CalendarLessonsController::class)
    ->name('admin.calendar.lessons');

Route::middleware(['web', 'auth'])
    ->get('/enrollments/{enrollment}/contract/print', [EnrollmentContractController::class, 'print'])
    ->name('enrollments.contract.print');

Route::get('/google/oauth/redirect', [GoogleOAuthController::class, 'redirect']);
Route::get('/google/oauth/callback', [GoogleOAuthController::class, 'callback']);

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});
