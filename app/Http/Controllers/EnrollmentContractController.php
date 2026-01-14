<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;

class EnrollmentContractController extends Controller
{
    public function print(Enrollment $enrollment)
    {
        $enrollment->load([
            'student',
            'course.subject',   // <-- lingua
        ]);

        return view('contracts.enrollment', [
            'enrollment' => $enrollment,
            'student'    => $enrollment->student,
            'course'     => $enrollment->course,
            'subject'    => $enrollment->course?->subject,
        ]);
    }
}
