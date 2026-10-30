<?php

namespace App\Http\Controllers;

use App\Models\Result;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminResultController extends Controller
{
    public function index(): View
    {
        $students = User::where('role', 'student')
            ->whereHas('results', fn ($query) => $query->where('status', '!=', Result::STATUS_DRAFT))
            ->with(['results' => function ($query): void {
                $query->where('status', '!=', Result::STATUS_DRAFT)
                    ->with('course.teacher')
                    ->orderBy('course_id')
                    ->orderBy('id');
            }])
            ->orderBy('full_name')
            ->orderBy('id')
            ->paginate(15);
        $scoreFields = Result::SCORE_FIELDS;
        $scoreMaximums = Result::SCORE_MAXIMUMS;

        return view('admin.results.index', compact('students', 'scoreFields', 'scoreMaximums'));
    }

    public function approve(Result $result): RedirectResponse
    {
        DB::transaction(function () use ($result): void {
            $result = Result::query()->lockForUpdate()->findOrFail($result->id);
            abort_unless($result->status === Result::STATUS_PENDING_REVIEW, 409, 'Only pending results can be approved.');

            $result->status = Result::STATUS_APPROVED;
            $result->reviewed_at = now();
            $result->admin_feedback = null;
            $result->save();
        });

        return redirect()->route('admin.results.index')->with('status', 'Result approved and published to the student.');
    }

    public function returnForEdits(Request $request, Result $result): RedirectResponse
    {
        $data = $request->validate([
            'feedback' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($result, $data): void {
            $result = Result::query()->lockForUpdate()->findOrFail($result->id);
            abort_unless($result->status === Result::STATUS_PENDING_REVIEW, 409, 'Only pending results can be returned.');

            $result->status = Result::STATUS_RETURNED;
            $result->reviewed_at = now();
            $result->admin_feedback = $data['feedback'];
            $result->save();
        });

        return redirect()->route('admin.results.index')->with('status', 'Result returned to the teacher for edits.');
    }
}
