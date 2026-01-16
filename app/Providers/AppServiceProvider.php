<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Lesson;
use App\Observers\LessonObserver;
use App\Models\Enrollment;
use App\Observers\EnrollmentObserver;
use App\Models\Student;
use App\Models\Teacher;
use App\Observers\StudentObserver;
use App\Observers\TeacherObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ✅ Superadmin bypassa tutte le permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });

        Lesson::observe(LessonObserver::class);
        Enrollment::observe(EnrollmentObserver::class);
        Student::observe(StudentObserver::class);
         Teacher::observe(TeacherObserver::class);
    }
}
