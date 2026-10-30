@extends('layouts.app')

@section('title', 'My courses')
@section('content')
<div class="page-heading"><div><p class="eyebrow">TEACHER PORTAL</p><h1>My courses</h1><p class="muted">Courses assigned to your account.</p></div><a class="button primary" href="{{ route('teacher.results.index') }}">Enter results</a></div>
<section class="card-grid">
    @forelse($courses as $course)
        <article class="panel course-card"><span class="badge">{{ $course->course_code }}</span><h2>{{ $course->course_name }}</h2><p>{{ $course->description ?: 'No course description.' }}</p><div class="course-meta">{{ $course->credit_hours }} credit hours · {{ $course->academic_year ? $course->academic_year.' · Semester '.$course->semester.' · ' : '' }}{{ $course->students_count }} enrolled</div></article>
    @empty<div class="panel empty">No courses are assigned to you yet. Ask an administrator to assign one.</div>@endforelse
</section>
@endsection
