@extends('layouts.app')

@section('title', 'Result submissions')
@section('content')
<div class="page-heading">
    <div><p class="eyebrow">ADMINISTRATION</p><h1>Result submissions</h1><p class="muted">Review results submitted by teachers. Approved results become visible to students.</p></div>
</div>
<section class="panel table-wrap">
    <table class="results-table score-sheet admin-results-table">
        <thead>
            <tr>
                <th scope="col">Student</th>
                <th scope="col">Course</th>
                <th scope="col">Teacher</th>
                @include('results._score-fields-head')
                <th scope="col">Total</th>
                <th scope="col">Grade</th>
                <th scope="col">Submitted</th>
            </tr>
        </thead>
        <tbody>
        @forelse($students as $student)
            @foreach($student->results as $result)
                <tr>
                    @if($loop->first)
                        <td rowspan="{{ $student->results->count() }}">{{ $student->full_name }}</td>
                    @endif
                    <td>{{ $result->course->course_name }}</td>
                    <td>{{ $result->course->teacher?->full_name ?? 'Unassigned' }}</td>
                    @foreach($scoreFields as $field => $label)<td>{{ $result->{$field} ?? '—' }}</td>@endforeach
                    <td>{{ $result->total }} / 100</td>
                    <td>{{ $result->grade }}</td>
                    <td>{{ $result->submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        @empty
            <tr><td colspan="{{ count($scoreFields) + 6 }}" class="empty">No result submissions yet.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div class="pagination">{{ $students->links() }}</div>
</section>
@endsection
