@extends('layouts.app')

@section('title', 'Courses')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ADMINISTRATION</p><h1>Courses</h1><p class="muted">Create courses and assign teachers.</p></div></div>
<section class="panel">
    <div class="panel-heading"><h2>Add a course</h2></div>
    <form method="POST" action="{{ route('admin.courses.store') }}" class="form-grid">
        @csrf
        <label>Course name<input name="course_name" value="{{ old('course_name') }}" required></label>
        <label>Course code<input name="course_code" value="{{ old('course_code') }}" required></label>
        <label>Credit hours<input type="number" name="credit_hours" min="0" max="100" value="{{ old('credit_hours', 3) }}" required></label>
        <label>Academic year<input name="academic_year" placeholder="2026/27" pattern="[0-9]{4}/[0-9]{2}" value="{{ old('academic_year') }}" required></label>
        <label>Semester<select name="semester" required><option value="">Select a semester</option><option value="1" @selected(old('semester') == 1)>Semester 1</option><option value="2" @selected(old('semester') == 2)>Semester 2</option></select></label>
        <label>Teacher<select name="teacher_id"><option value="">Unassigned</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->full_name }}</option>@endforeach</select></label>
        <label class="span-2">Description<textarea name="description" rows="3">{{ old('description') }}</textarea></label>
        <div class="span-2"><button class="button primary" type="submit">Create course</button></div>
    </form>
</section>
<section class="panel table-wrap">
    <div class="panel-heading"><h2>All courses</h2></div>
    <table><thead><tr><th>Course</th><th>Code</th><th>Year / semester</th><th>Credits</th><th>Teacher</th><th></th></tr></thead><tbody>
    @forelse($courses as $course)
        <tr>
            <td><strong>{{ $course->course_name }}</strong><div class="muted">{{ $course->description }}</div></td>
            <td>{{ $course->course_code }}</td>
            <td>{{ $course->academic_year ? $course->academic_year.' · Semester '.$course->semester : 'Not assigned' }}</td>
            <td>{{ $course->credit_hours }}</td>
            <td>{{ $course->teacher?->full_name ?? 'Unassigned' }}</td>
            <td>
                <form method="POST" action="{{ route('admin.courses.academic-term.update', $course) }}" class="course-term-form">
                    @csrf
                    @method('PUT')
                    <label>Year<input name="academic_year" placeholder="2026/27" pattern="[0-9]{4}/[0-9]{2}" value="{{ old('course_id') == $course->id ? old('academic_year') : $course->academic_year }}" required></label>
                    <label>Semester<select name="semester" required><option value="1" @selected((old('course_id') == $course->id ? old('semester') : $course->semester) == 1)>1</option><option value="2" @selected((old('course_id') == $course->id ? old('semester') : $course->semester) == 2)>2</option></select></label>
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <button class="button secondary small" type="submit">Save term</button>
                </form>
                <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('Delete this course and its enrollments/results?')">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form>
            </td>
        </tr>
    @empty<tr><td colspan="6" class="empty">No courses have been created.</td></tr>@endforelse
    </tbody></table><div class="pagination">{{ $courses->links() }}</div>
</section>
@endsection
