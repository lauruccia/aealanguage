<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Lesson;
use App\Observers\LessonObserver;
use App\Models\Enrollment;
use App\Observers\EnrollmentObserver;
use App\Models\Student;
use App\Observers\StudentObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Lesson::observe(LessonObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        Student::observe(StudentObserver::class);



    }
}
