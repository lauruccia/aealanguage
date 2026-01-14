<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CalendarLessonsController;
use App\Http\Controllers\EnrollmentContractController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web', 'auth'])
    ->get('/admin/calendar/lessons', CalendarLessonsController::class)
    ->name('admin.calendar.lessons');

Route::middleware(['web', 'auth'])
    ->get('/enrollments/{enrollment}/contract/print', [EnrollmentContractController::class, 'print'])
    ->name('enrollments.contract.print');
