@extends('layouts.app')

@section('title', 'Create account')
@section('content')
<section class="login-wrap">
    <div class="login-card">
        <p class="eyebrow">JOIN CAMPUSDESK</p>
        <h1>Create your student account</h1>
        <p class="muted">Register to browse courses and manage your academic profile.</p>
        <form method="POST" action="{{ route('register.store') }}" class="stack">
            @csrf
            <label>Full name<input name="full_name" value="{{ old('full_name') }}" required maxlength="100" autocomplete="name" autofocus></label>
            <label>Username<input name="username" value="{{ old('username') }}" required maxlength="50" autocomplete="username"></label>
            <label>Email address (optional)<input type="email" name="email" value="{{ old('email') }}" maxlength="100" autocomplete="email"></label>
            <label>Password<input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
            <label>Confirm password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>
            <button class="button primary full" type="submit">Create account</button>
        </form>
        <p class="hint">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</section>
@endsection
