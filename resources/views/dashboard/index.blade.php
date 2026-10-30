@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')
@if($user->role === 'student')
    <section class="student-dashboard">
        <header class="student-dashboard-banner">
            <div>
                <p class="eyebrow">STUDENT PORTAL</p>
                <h1>Academic Status</h1>
                <p>Welcome, {{ $user->full_name }}. Review your enrollment and course results.</p>
            </div>
            <span class="student-avatar" aria-hidden="true">{{ strtoupper(substr($user->full_name, 0, 1)) }}</span>
        </header>

        <nav class="student-dashboard-tabs" aria-label="Student portal sections">
            <a href="{{ route('profile.edit') }}">Profile</a>
            <a href="{{ route('profile.edit') }}">Change profile</a>
            <a href="{{ route('student.courses.index') }}">Courses</a>
            <a href="{{ route('dashboard') }}" aria-current="page">Academic status</a>
            <a href="{{ route('student.results.index') }}">My results</a>
        </nav>

        <div class="student-registration-summary">
            <p>Currently you are enrolled in <strong>{{ $studentCourses->count() }} {{ \Illuminate\Support\Str::plural('course', $studentCourses->count()) }}</strong>.</p>
            <a class="button secondary small" href="{{ route('student.courses.index') }}">Manage courses</a>
        </div>

        <section class="student-stat-grid" aria-label="Academic overview">
            @foreach($stats as $label => $value)
                <article class="student-stat-card">
                    <span>{{ $label }}</span>
                    <strong>{{ $value }}</strong>
                </article>
            @endforeach
        </section>

        <div class="student-academic-layout">
            <section class="student-academic-panel">
                <div class="student-section-heading">
                    <div><p class="eyebrow">ACADEMIC RECORD</p><h2>Student Academic Status Summary</h2></div>
                    <div>
                        <a class="button secondary small" href="{{ route('student.results.export') }}">Download CSV</a>
                        <a href="{{ route('student.results.index') }}">All results</a>
                    </div>
                </div>
                @forelse($studentSemesters as $semester)
                    <article class="student-semester-card">
                        <div class="student-section-heading">
                            <h3>{{ $semester['title'] }}</h3>
                        </div>
                        <div class="student-semester-summary">
                            <div><span>SCR</span><strong>{{ $semester['semester_credits'] }}</strong></div>
                            <div><span>SGP</span><strong>{{ number_format($semester['semester_grade_points'], 2) }}</strong></div>
                            <div><span>SGPA</span><strong>{{ $semester['semester_gpa'] === null ? '—' : number_format($semester['semester_gpa'], 2) }}</strong></div>
                            <div><span>CCR</span><strong>{{ $semester['cumulative_credits'] }}</strong></div>
                            <div><span>CGP</span><strong>{{ number_format($semester['cumulative_grade_points'], 2) }}</strong></div>
                            <div><span>CGPA</span><strong>{{ $semester['cumulative_gpa'] === null ? '—' : number_format($semester['cumulative_gpa'], 2) }}</strong></div>
                            <div><span>Status</span><strong class="semester-status {{ strtolower($semester['status']) }}">{{ $semester['status'] }}</strong></div>
                        </div>
                        @foreach($semester['courses'] as $result)
                            <div class="student-semester-result">
                                <div class="student-result-heading">
                                    <div>
                                        <h4>{{ $result->course->course_name }}</h4>
                                        <p>{{ $result->course->course_code }} · {{ $result->course->credit_hours }} credit hours · {{ ucwords(str_replace('_', ' ', $result->status)) }}</p>
                                    </div>
                                    <span class="badge">Grade {{ $result->grade }} · {{ number_format((float) $result->total, 2) }}/100</span>
                                </div>
                                <div class="student-score-breakdown">
                                    @foreach($scoreFields as $field => $label)
                                        <span>{{ $label }} <strong>{{ number_format((float) $result->{$field}, 2) }}/{{ $scoreMaximums[$field] }}</strong></span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </article>
                @empty
                    <p class="student-empty-state">No results are available yet. Your teacher's saved results will appear here.</p>
                @endforelse
            </section>

            <aside class="student-academic-aside">
                <h2>Academic information</h2>
                <p>Your marks are shown out of 100 across Quiz / 5, Attendance / 5, Assignment / 10, Midterm / 30, and Final / 50.</p>
                @if($pendingResultsCount > 0)
                    <p class="student-pending-note">{{ $pendingResultsCount }} {{ \Illuminate\Support\Str::plural('result', $pendingResultsCount) }} awaiting administrator review. You can already view the result while it is under review.</p>
                @else
                    <p>Results are displayed as soon as the teacher saves them. Their review status is shown with each grade.</p>
                @endif
                <a class="button primary small" href="{{ route('student.results.index') }}">View all results</a>
            </aside>
        </div>

        <aside class="student-important-notes" aria-labelledby="student-important-notes-title">
            <span class="student-important-notes-icon" aria-hidden="true">i</span>
            <div>
                <h2 id="student-important-notes-title">Important Notes:</h2>
                <p>This page shows your academic status for each semester. Updates made by your instructor appear here when results are saved.</p>
                <p>Check this page after exams and whenever a result is updated. Results awaiting administrator review are visible, but are not yet final.</p>
                <p>Once an administrator approves a course result, it is treated as final. If you believe a grade needs correction or re-grading, contact your administrator.</p>
                <p>Check “My enrolled courses” below to confirm your current semester courses are listed. If a course is missing or incorrect, contact your administrator.</p>
                <p>If a course result is missing, contact your instructor to confirm it has been saved.</p>
            </div>
        </aside>

        <section class="student-enrollment-panel">
            <div class="student-section-heading">
                <div><p class="eyebrow">REGISTRATION</p><h2>My enrolled courses</h2></div>
                <a href="{{ route('student.courses.index') }}">Browse courses</a>
            </div>
            @forelse($studentCourses as $course)
                <div class="student-enrollment-row">
                    <div><strong>{{ $course->course_name }}</strong><span>{{ $course->course_code }} · {{ $course->credit_hours }} credit hours · {{ $course->academic_year ? $course->academic_year.' · Semester '.$course->semester : 'Semester not assigned' }}</span></div>
                    <span>{{ $course->teacher?->full_name ?? 'Teacher not assigned' }}</span>
                </div>
            @empty
                <p class="student-empty-state">You are not enrolled in any courses yet. Browse courses to get started.</p>
            @endforelse
        </section>
    </section>
@else
    <section class="staff-dashboard student-dashboard">
        <header class="student-dashboard-banner">
            <div>
                <p class="eyebrow">{{ strtoupper($user->role) }} PORTAL</p>
                <h1>{{ $user->role === 'admin' ? 'Administration overview' : 'Teacher overview' }}</h1>
                <p>Welcome, {{ $user->full_name }}. {{ $user->role === 'admin' ? 'Manage accounts, courses, and result reviews.' : 'Review your courses and manage student results.' }}</p>
            </div>
            <span class="student-avatar" aria-hidden="true">{{ strtoupper(substr($user->full_name, 0, 1)) }}</span>
        </header>

        <nav class="student-dashboard-tabs" aria-label="{{ ucfirst($user->role) }} portal sections">
            <a href="{{ route('dashboard') }}" aria-current="page">Dashboard</a>
            @if($user->role === 'admin')
                <a class="admin-dashboard-tab admin-tab-students" href="{{ route('admin.users.index', ['role' => 'student']) }}"><span class="admin-tab-icon" aria-hidden="true">S</span>Students</a>
                <a class="admin-dashboard-tab admin-tab-teachers" href="{{ route('admin.users.index', ['role' => 'teacher']) }}"><span class="admin-tab-icon" aria-hidden="true">T</span>Teachers</a>
                <a class="admin-dashboard-tab admin-tab-courses" href="{{ route('admin.courses.index') }}"><span class="admin-tab-icon" aria-hidden="true">C</span>Courses</a>
                <a class="admin-dashboard-tab admin-tab-results" href="{{ route('admin.results.index') }}"><span class="admin-tab-icon" aria-hidden="true">R</span>Result submissions</a>
            @else
                <a href="{{ route('teacher.courses.index') }}">My courses</a>
                <a href="{{ route('teacher.results.index') }}">Results</a>
            @endif
            <a href="{{ route('profile.edit') }}">Profile</a>
        </nav>

        <section class="staff-overview">
            <div class="student-section-heading">
                <div><p class="eyebrow">AT A GLANCE</p><h2>{{ ucfirst($user->role) }} dashboard</h2></div>
            </div>
            <div class="staff-stat-grid">
                @foreach($stats as $label => $value)
                    <article class="student-stat-card"><span>{{ $label }}</span><strong>{{ $value }}</strong></article>
                @endforeach
            </div>
        </section>

        <section class="student-enrollment-panel staff-quick-panel">
            <div class="student-section-heading">
                <div><p class="eyebrow">WORKSPACE</p><h2>Quick actions</h2></div>
            </div>
        <div class="quick-grid">
            @if($user->role === 'admin')
                <a class="quick-card admin-dashboard-tab admin-tab-students" href="{{ route('admin.users.create', ['role' => 'student']) }}"><strong>Add a student</strong><span>Create a student account</span></a>
                <a class="quick-card admin-dashboard-tab admin-tab-teachers" href="{{ route('admin.users.create', ['role' => 'teacher']) }}"><strong>Add a teacher</strong><span>Create a teacher account</span></a>
                <a class="quick-card admin-dashboard-tab admin-tab-courses" href="{{ route('admin.courses.index') }}"><strong>Manage courses</strong><span>Assign teachers and review courses</span></a>
                <a class="quick-card admin-dashboard-tab admin-tab-results" href="{{ route('admin.results.index') }}"><strong>Review results</strong><span>Approve or return teacher submissions</span></a>
            @else
                <a class="quick-card" href="{{ route('teacher.courses.index') }}"><strong>My courses</strong><span>Review your course assignments</span></a>
                <a class="quick-card" href="{{ route('teacher.results.index') }}"><strong>Enter results</strong><span>Record student assessments</span></a>
            @endif
            <a class="quick-card" href="{{ route('profile.edit') }}"><strong>Update profile</strong><span>Change your contact information</span></a>
        </div>
        </section>
    </section>
@endif
@endsection
