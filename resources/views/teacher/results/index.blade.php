@extends('layouts.app')

@section('title', 'Student results')
@section('content')
<div class="page-heading"><div><p class="eyebrow">TEACHER PORTAL</p><h1>Student results</h1><p class="muted">Enter marks for each assessment category.</p></div></div>
<section class="panel">
    <div class="panel-heading"><h2>Record or update a result</h2></div>
    <form method="POST" action="{{ route('teacher.results.store') }}">
        @csrf
        <div class="form-grid">
            <label>Student<select name="student_id" required><option value="">Select a student</option>@foreach($students as $student)<option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>{{ $student->full_name }} ({{ $student->username }})</option>@endforeach</select></label>
            <label>Course<select name="course_id" required><option value="">Select a course</option>@foreach($courses as $course)<option value="{{ $course->id }}" @selected(old('course_id') == $course->id)>{{ $course->course_name }} — {{ $course->course_code }}{{ $course->academic_year ? ' · '.$course->academic_year.' Semester '.$course->semester : '' }}</option>@endforeach</select></label>
        </div>
        <div class="score-entry-wrap">
            <table class="score-entry-table">
                <thead>
                    <tr>@include('results._score-fields-head')</tr>
                </thead>
                <tbody><tr>
                    @foreach($scoreFields as $field => $label)
                        <td><label class="score-input-label"><span class="visually-hidden">{{ $label }}</span><input aria-label="{{ $label }}" type="number" name="{{ $field }}" min="0" max="{{ $scoreMaximums[$field] }}" step="0.01" value="{{ old($field) }}" required></label></td>
                    @endforeach
                </tr></tbody>
            </table>
        </div>
        <div class="result-form-actions">
            <button class="button primary score-save-button" type="submit" name="action" value="save">Save result</button>
            <button class="button secondary score-save-button" type="submit" name="action" value="submit">Submit to admin</button>
        </div>
    </form>
    <p class="hint">Total is out of 100: Quiz / 5, Attendance / 5, Assignment / 10, Midterm / 30, and Final / 50. Grades: A ≥ 90, B ≥ 80, C ≥ 70, D ≥ 60, otherwise F. A student must be enrolled in the selected course.</p>
</section>
<section class="panel table-wrap">
    <div class="panel-heading"><h2>Recent results</h2></div>
    <table class="results-table score-sheet">
        <thead>
            <tr>
                <th scope="col">Student</th>
                <th scope="col">Courses</th>
                @include('results._score-fields-head')
                <th scope="col">Total</th>
                <th scope="col">Grade</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
    @forelse($results as $result)
        <tr>
            <td>{{ $result->student->full_name }}</td>
            <td>{{ $result->course->course_name }}</td>
            @foreach($scoreFields as $field => $label)<td>{{ $result->{$field} ?? '—' }}</td>@endforeach
            <td>{{ $result->total }} / 100</td>
            <td><span class="badge">{{ $result->grade }}</span></td>
            <td>{{ ucwords(str_replace('_', ' ', $result->status)) }}</td>
            <td>
                @if($result->status === \App\Models\Result::STATUS_RETURNED && $result->admin_feedback)
                    <p class="hint">Admin feedback: {{ $result->admin_feedback }}</p>
                @endif
                @if($result->canBeEditedByTeacher())
                    <details class="result-update" open>
                        <summary class="button secondary small">Edit result</summary>
                        <form method="POST" action="{{ route('teacher.results.update', $result) }}" class="result-update-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="editing_result_id" value="{{ $result->id }}">
                            <div class="score-entry-wrap">
                                <table class="score-entry-table">
                                    <thead><tr>@include('results._score-fields-head')</tr></thead>
                                    <tbody><tr>
                                        @foreach($scoreFields as $field => $label)
                                            <td><label class="score-input-label"><span class="visually-hidden">{{ $label }}</span><input aria-label="{{ $label }}" type="number" name="{{ $field }}" min="0" max="{{ $scoreMaximums[$field] }}" step="0.01" value="{{ old('editing_result_id') == $result->id ? old($field, $result->{$field} ?? 0) : ($result->{$field} ?? 0) }}" required></label></td>
                                        @endforeach
                                    </tr></tbody>
                                </table>
                            </div>
                            <div class="result-form-actions">
                                <button class="button primary score-save-button" type="submit" name="action" value="save">Update result</button>
                                <button class="button secondary score-save-button" type="submit" name="action" value="submit">Submit to admin</button>
                            </div>
                        </form>
                    </details>
                @else
                    <span class="muted">Locked after submission</span>
                @endif
            </td>
        </tr>
    @empty<tr><td colspan="{{ count($scoreFields) + 6 }}" class="empty">No results have been entered.</td></tr>@endforelse
    </tbody></table><div class="pagination">{{ $results->links() }}</div>
</section>
@endsection
