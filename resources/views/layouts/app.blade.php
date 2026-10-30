<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Student Registrar System') · Student Registrar System</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@auth
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand sidebar-brand" href="{{ route('dashboard') }}"><img src="{{ asset('favicon.svg') }}" alt=""><span class="brand-name">Student Registrar System</span></a>

            <p class="sidebar-label">WORKSPACE</p>
            <nav class="sidebar-nav" aria-label="Main navigation">
                <a @class(['admin-sidebar-dashboard' => auth()->user()->role === 'admin', 'portal-sidebar-dashboard' => auth()->user()->role !== 'admin']) href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                    <span class="sidebar-icon" aria-hidden="true">D</span>Dashboard
                </a>
                @if(auth()->user()->role === 'admin')
                    <a class="admin-sidebar-students" href="{{ route('admin.users.index', ['role' => 'student']) }}" @if(request()->routeIs('admin.users.*') && request('role', 'student') === 'student') aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">S</span>Students
                    </a>
                    <a class="admin-sidebar-teachers" href="{{ route('admin.users.index', ['role' => 'teacher']) }}" @if(request()->routeIs('admin.users.*') && request('role') === 'teacher') aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">T</span>Teachers
                    </a>
                    <a class="admin-sidebar-courses" href="{{ route('admin.courses.index') }}" @if(request()->routeIs('admin.courses.*')) aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">C</span>Courses
                    </a>
                    <a class="admin-sidebar-results" href="{{ route('admin.results.index') }}" @if(request()->routeIs('admin.results.*')) aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">R</span>Result submissions
                    </a>
                @elseif(auth()->user()->role === 'teacher')
                    <a class="portal-sidebar-courses" href="{{ route('teacher.courses.index') }}" @if(request()->routeIs('teacher.courses.*')) aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">C</span>My courses
                    </a>
                    <a class="portal-sidebar-results" href="{{ route('teacher.results.index') }}" @if(request()->routeIs('teacher.results.*')) aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">R</span>Results
                    </a>
                @else
                    <a class="portal-sidebar-courses" href="{{ route('student.courses.index') }}" @if(request()->routeIs('student.courses.*')) aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">C</span>Courses
                    </a>
                    <a class="portal-sidebar-results" href="{{ route('student.results.index') }}" @if(request()->routeIs('student.results.*')) aria-current="page" @endif>
                        <span class="sidebar-icon" aria-hidden="true">R</span>Results
                    </a>
                @endif
            </nav>

            <div class="sidebar-account">
                <a class="account-link portal-sidebar-profile" href="{{ route('profile.edit') }}" @if(request()->routeIs('profile.*')) aria-current="page" @endif>
                    <span class="account-avatar">{{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}</span>
                    <span class="account-details">
                        <strong>{{ auth()->user()->full_name }}</strong>
                        <small>{{ ucfirst(auth()->user()->role) }} account</small>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="sign-out-button portal-sidebar-logout" type="submit"><span class="sidebar-icon" aria-hidden="true">&gt;</span>Sign out</button>
                </form>
            </div>
        </aside>

        <div class="workspace">
            <main class="workspace-content">
                @if(session('status'))<div class="notice success">{{ session('status') }}</div>@endif
                @if($errors->any())
                    <div class="notice error"><strong>Please check the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif
                @yield('content')
            </main>
            <footer class="footer">Student Registrar System</footer>
        </div>
    </div>
@else
    <header class="topbar">
        <a class="brand" href="{{ route('login') }}"><img src="{{ asset('favicon.svg') }}" alt=""><span class="brand-name">Student Registrar System</span></a>
    </header>
    <main class="container">
        @if(session('status'))<div class="notice success">{{ session('status') }}</div>@endif
        @if($errors->any())
            <div class="notice error"><strong>Please check the form:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @yield('content')
    </main>
    <footer class="footer">Student Registrar System</footer>
@endauth
</body>
</html>
