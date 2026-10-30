<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Result;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
    public function teacherIndex(): View
    {
        $courses = Course::where('teacher_id', auth()->id())->orderBy('course_name')->get();
        $students = User::where('role', 'student')->orderBy('full_name')->get();
        $scoreFields = Result::SCORE_FIELDS;
        $scoreMaximums = Result::SCORE_MAXIMUMS;
        $results = Result::with(['student', 'course'])
            ->whereHas('course', fn ($query) => $query->where('teacher_id', auth()->id()))
            ->latest('updated_at')
            ->paginate(15);

        return view('teacher.results.index', compact('courses', 'students', 'results', 'scoreFields', 'scoreMaximums'));
    }

    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'action' => ['required', 'in:save,submit'],
        ];

        $data = $request->validate([...$rules, ...$this->scoreValidationRules()]);

        abort_unless(User::whereKey($data['student_id'])->where('role', 'student')->exists(), 422, 'Select a student account.');
        $course = Course::whereKey($data['course_id'])->where('teacher_id', auth()->id())->firstOrFail();
        abort_unless(DB::table('enrollments')->where('student_id', $data['student_id'])->where('course_id', $course->id)->exists(), 422, 'The student must be enrolled in this course.');

        $scores = array_intersect_key($data, Result::SCORE_FIELDS);
        $resultData = $this->calculateTotalAndGrade($scores);

        $result = DB::transaction(function () use ($data, $scores, $resultData, $course): Result {
            $result = Result::query()
                ->where('student_id', $data['student_id'])
                ->where('course_id', $course->id)
                ->lockForUpdate()
                ->first();

            if ($result !== null) {
                abort_unless($result->canBeEditedByTeacher(), 409, 'This result has already been submitted and is locked.');
            } else {
                $result = new Result([
                    'student_id' => $data['student_id'],
                    'course_id' => $course->id,
                ]);
            }

            $result->fill([...$scores, ...$resultData]);

            if ($data['action'] === 'submit') {
                $this->markSubmitted($result);
            }

            $result->save();

            return $result;
        });

        ['total' => $total, 'grade' => $grade] = $resultData;
        $message = $data['action'] === 'submit' ? 'Result submitted to the administrator' : 'Result saved as a draft';

        return redirect()->route('teacher.results.index')
            ->with('status', sprintf('%s: %.2f/100 (%s).', $message, $total, $grade));
    }

    public function update(Request $request, Result $result): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:save,submit'],
            ...$this->scoreValidationRules(),
        ]);
        $scores = array_intersect_key($data, Result::SCORE_FIELDS);
        ['total' => $total, 'grade' => $grade] = $this->calculateTotalAndGrade($scores);

        DB::transaction(function () use ($result, $scores, $total, $grade, $data): void {
            $result = Result::query()->lockForUpdate()->findOrFail($result->id);
            $course = Course::whereKey($result->course_id)
                ->where('teacher_id', auth()->id())
                ->firstOrFail();

            abort_unless($result->canBeEditedByTeacher(), 409, 'This result has already been submitted and is locked.');
            abort_unless(
                DB::table('enrollments')->where('student_id', $result->student_id)->where('course_id', $course->id)->exists(),
                422,
                'The student must be enrolled in this course.',
            );

            $result->fill([...$scores, 'total' => $total, 'grade' => $grade]);

            if ($data['action'] === 'submit') {
                $this->markSubmitted($result);
            }

            $result->save();
        });

        $message = $data['action'] === 'submit'
            ? 'Result updated and submitted to the administrator'
            : 'Result updated';

        return redirect()->route('teacher.results.index')
            ->with('status', sprintf('%s: %.2f/100 (%s).', $message, $total, $grade));
    }

    public function studentIndex(): View
    {
        $results = Result::with('course.teacher')
            ->where('student_id', auth()->id())
            ->orderBy('course_id')
            ->paginate(15);
        $scoreFields = Result::SCORE_FIELDS;
        $scoreMaximums = Result::SCORE_MAXIMUMS;

        return view('student.results.index', compact('results', 'scoreFields', 'scoreMaximums'));
    }

    public function studentExport(): StreamedResponse
    {
        $results = Result::with('course')
            ->where('student_id', auth()->id())
            ->orderBy('course_id')
            ->get();

        return response()->streamDownload(function () use ($results): void {
            $stream = fopen('php://output', 'w');

            if ($stream === false) {
                throw new \RuntimeException('Unable to open the CSV output stream.');
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, [
                'Course Code',
                'Course Name',
                'Academic Year',
                'Semester',
                'Credit Hours',
                'Quiz / 5',
                'Attendance / 5',
                'Assignment / 10',
                'Midterm / 30',
                'Final / 50',
                'Total / 100',
                'Grade',
            ], ',', '"', '');

            foreach ($results as $result) {
                $course = $result->course;

                fputcsv($stream, [
                    $this->spreadsheetSafeText($course->course_code),
                    $this->spreadsheetSafeText($course->course_name),
                    $this->spreadsheetSafeText($course->academic_year),
                    $course->semester === null ? '' : (string) $course->semester,
                    (string) $course->credit_hours,
                    ...array_map(
                        fn (string $field): string => $result->{$field} === null
                            ? ''
                            : number_format((float) $result->{$field}, 2, '.', ''),
                        array_keys(Result::SCORE_FIELDS),
                    ),
                    number_format((float) $result->total, 2, '.', ''),
                    $this->spreadsheetSafeText($result->grade),
                ], ',', '"', '');
            }

            fclose($stream);
        }, 'student-results.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function scoreValidationRules(): array
    {
        $rules = [];

        foreach (Result::SCORE_MAXIMUMS as $field => $maximum) {
            $rules[$field] = ['required', 'numeric', 'between:0,'.$maximum];
        }

        return $rules;
    }

    /**
     * @param  array<string, int|float|string>  $scores
     * @return array{total: float, grade: string}
     */
    private function calculateTotalAndGrade(array $scores): array
    {
        $total = round(array_sum(array_map('floatval', $scores)), 2);
        $grade = match (true) {
            $total >= 90 => 'A',
            $total >= 80 => 'B',
            $total >= 70 => 'C',
            $total >= 60 => 'D',
            default => 'F',
        };

        return ['total' => $total, 'grade' => $grade];
    }

    private function markSubmitted(Result $result): void
    {
        $result->status = Result::STATUS_PENDING_REVIEW;
        $result->submitted_at = now();
        $result->reviewed_at = null;
        $result->admin_feedback = null;
    }

    private function spreadsheetSafeText(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[\x00-\x20]*[=+\-@]/', $value) === 1
            ? "'".$value
            : $value;
    }
}
