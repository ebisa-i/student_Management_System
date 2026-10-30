<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Result;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();
        $studentCourses = collect();
        $studentResults = collect();
        $studentSemesters = collect();
        $pendingResultsCount = 0;
        $averageMark = null;
        $scoreFields = Result::SCORE_FIELDS;
        $scoreMaximums = Result::SCORE_MAXIMUMS;

        if ($user->role === 'student') {
            $studentCourses = $user->courses()
                ->with('teacher')
                ->orderBy('course_name')
                ->get();
            $studentResults = Result::with('course')
                ->where('student_id', $user->id)
                ->orderBy('course_id')
                ->get();
            $cumulativeCredits = 0;
            $cumulativeGradePoints = 0.0;
            $studentSemesters = $studentResults
                ->groupBy(fn (Result $result): string => ($result->course->academic_year ?? 'not_assigned').'|'.($result->course->semester ?? ''))
                ->sort(function (Collection $firstSemester, Collection $secondSemester): int {
                    $firstCourse = $firstSemester->first()->course;
                    $secondCourse = $secondSemester->first()->course;

                    return [
                        $firstCourse->academic_year ?? '9999/99',
                        $firstCourse->semester ?? 99,
                    ] <=> [
                        $secondCourse->academic_year ?? '9999/99',
                        $secondCourse->semester ?? 99,
                    ];
                })
                ->map(function (Collection $semesterResults) use (&$cumulativeCredits, &$cumulativeGradePoints): array {
                    $firstCourse = $semesterResults->first()->course;
                    $semesterCredits = $semesterResults->sum(
                        fn (Result $result): int => (int) $result->course->credit_hours,
                    );
                    $semesterGradePoints = $semesterResults->sum(
                        fn (Result $result): float => (int) $result->course->credit_hours * $this->gradePointFor((float) $result->total),
                    );

                    $cumulativeCredits += $semesterCredits;
                    $cumulativeGradePoints += $semesterGradePoints;

                    return [
                        'title' => $firstCourse->academic_year === null
                            ? 'Semester not assigned'
                            : 'Academic Year '.$firstCourse->academic_year.' · Semester '.$firstCourse->semester,
                        'courses' => $semesterResults,
                        'semester_credits' => $semesterCredits,
                        'semester_grade_points' => $semesterGradePoints,
                        'semester_gpa' => $semesterCredits > 0 ? round($semesterGradePoints / $semesterCredits, 2) : null,
                        'cumulative_credits' => $cumulativeCredits,
                        'cumulative_grade_points' => $cumulativeGradePoints,
                        'cumulative_gpa' => $cumulativeCredits > 0 ? round($cumulativeGradePoints / $cumulativeCredits, 2) : null,
                        'status' => $semesterResults->every(fn (Result $result): bool => (float) $result->total >= 60) ? 'Pass' : 'Fail',
                    ];
                })
                ->values();
            $pendingResultsCount = Result::where('student_id', $user->id)
                ->where('status', Result::STATUS_PENDING_REVIEW)
                ->count();
            $averageMark = $studentResults->isEmpty()
                ? null
                : round($studentResults->avg(fn (Result $result): float => (float) $result->total), 2);
            $stats = [
                'Enrolled courses' => $studentCourses->count(),
                'Results available' => $studentResults->count(),
                'Average mark' => $averageMark === null ? '—' : number_format($averageMark, 2).'/100',
            ];
        } else {
            $stats = match ($user->role) {
                'admin' => [
                    'Students' => User::where('role', 'student')->count(),
                    'Teachers' => User::where('role', 'teacher')->count(),
                    'Courses' => Course::count(),
                    'Pending results' => Result::where('status', Result::STATUS_PENDING_REVIEW)->count(),
                ],
                default => [
                    'My courses' => Course::where('teacher_id', $user->id)->count(),
                    'Results entered' => Result::whereHas('course', fn ($query) => $query->where('teacher_id', $user->id))->count(),
                ],
            };
        }

        return view('dashboard.index', compact(
            'user',
            'stats',
            'studentCourses',
            'studentResults',
            'studentSemesters',
            'pendingResultsCount',
            'averageMark',
            'scoreFields',
            'scoreMaximums',
        ));
    }

    private function gradePointFor(float $total): float
    {
        return match (true) {
            $total >= 90 => 4.0,
            $total >= 80 => 3.0,
            $total >= 70 => 2.0,
            $total >= 60 => 1.0,
            default => 0.0,
        };
    }
}
