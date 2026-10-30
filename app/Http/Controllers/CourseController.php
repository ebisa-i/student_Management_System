<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function adminIndex(): View
    {
        $courses = Course::with('teacher')->orderBy('course_name')->paginate(15);
        $teachers = User::where('role', 'teacher')->orderBy('full_name')->get();

        return view('admin.courses.index', compact('courses', 'teachers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'course_name' => ['required', 'string', 'max:100'],
            'course_code' => ['required', 'string', 'max:20', 'unique:courses,course_code'],
            'credit_hours' => ['required', 'integer', 'min:0', 'max:100'],
            'academic_year' => ['required', 'string', 'regex:/^\d{4}\/\d{2}$/'],
            'semester' => ['required', 'integer', Rule::in([1, 2])],
            'description' => ['nullable', 'string', 'max:5000'],
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'teacher')],
        ]);

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('status', 'Course created.');
    }

    public function updateAcademicTerm(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'academic_year' => ['required', 'string', 'regex:/^\d{4}\/\d{2}$/'],
            'semester' => ['required', 'integer', Rule::in([1, 2])],
        ]);

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('status', 'Course academic term updated.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return redirect()->route('admin.courses.index')->with('status', 'Course deleted.');
    }

    public function teacherIndex(): View
    {
        $courses = Course::where('teacher_id', auth()->id())->withCount('students')->orderBy('course_name')->get();

        return view('teacher.courses.index', compact('courses'));
    }

    public function studentIndex(): View
    {
        $courses = Course::with('teacher')->orderBy('course_name')->get();
        $enrolledIds = DB::table('enrollments')->where('student_id', auth()->id())->pluck('course_id')->all();

        return view('student.courses.index', compact('courses', 'enrolledIds'));
    }

    public function enroll(Course $course): RedirectResponse
    {
        $enrollmentCreated = DB::table('enrollments')->insertOrIgnore([
            'student_id' => auth()->id(),
            'course_id' => $course->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $message = $enrollmentCreated === 1
            ? 'You are enrolled in '.$course->course_name.'.'
            : 'You are already enrolled in '.$course->course_name.'.';

        return redirect()->route('student.courses.index')->with('status', $message);
    }
}
