@extends('layouts.app')

@section('title', 'My results')
@section('content')
<div class="page-heading"><div><p class="eyebrow">STUDENT PORTAL</p><h1>My results</h1><p class="muted">Results appear here as soon as your teacher saves them.</p></div></div>
<section class="panel table-wrap">
    <table class="results-table score-sheet student-results-table">
        <thead>
            <tr>
                <th scope="col">Courses</th>
                @include('results._score-fields-head')
                <th scope="col">Total</th>
                <th scope="col">Grade</th>
            </tr>
        </thead>
        <tbody>
    @forelse($results as $result)
        <tr><td><strong>{{ $result->course->course_name }}</strong><div class="muted">{{ $result->course->course_code }}</div></td>@foreach($scoreFields as $field => $label)<td>{{ $result->{$field} === null ? '—' : number_format((float) $result->{$field}, 2) }}</td>@endforeach<td><strong>{{ number_format((float) $result->total, 2) }}/100</strong></td><td><span class="badge">{{ $result->grade }}</span></td></tr>
    @empty<tr><td colspan="{{ count($scoreFields) + 3 }}" class="empty">No results are available yet. Your teacher's saved results will appear here.</td></tr>@endforelse
    </tbody></table><div class="pagination">{{ $results->links() }}</div>
</section>
@endsection
