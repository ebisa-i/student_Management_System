@extends('layouts.app')

@section('title', 'Courses')
@section('content')
<div class="page-heading"><div><p class="eyebrow">STUDENT PORTAL</p><h1>Course catalog</h1><p class="muted">Browse available courses and manage your enrollments.</p></div></div>
<section class="card-grid">
    @forelse($courses as $course)
        <article class="panel course-card"><span class="badge">{{ $course->course_code }}</span><h2>{{ $course->course_name }}</h2><p>{{ $course->description ?: 'No course description.' }}</p><div class="course-meta">{{ $course->credit_hours }} credit hours · {{ $course->academic_year ? $course->academic_year.' · Semester '.$course->semester.' · ' : '' }}Teacher: {{ $course->teacher?->full_name ?? 'Not assigned' }}</div>
            @if(in_array($course->id, $enrolledIds))<span class="status-pill">Enrolled</span>@else<form method="POST" action="{{ route('student.courses.enroll', $course) }}">@csrf<button class="button primary small" type="submit">Enroll</button></form>@endif
        </article>
    @empty<div class="panel empty">No courses are available yet.</div>@endforelse
</section>
@endsection
