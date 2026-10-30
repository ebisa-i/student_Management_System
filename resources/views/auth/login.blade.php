@extends('layouts.app')

@section('title', 'Sign in')
@section('content')
<section class="login-wrap">
    <div class="login-card">
        <p class="eyebrow">WELCOME BACK</p>
        <h1>Sign in to your account</h1>
        <p class="muted">Manage courses, students, and academic results.</p>
        <form method="POST" action="{{ route('login') }}" class="stack">
            @csrf
            <label>Username<input name="username" value="{{ old('username') }}" required autofocus autocomplete="username"></label>
            <label>Password<input type="password" name="password" required autocomplete="current-password"></label>
            <label class="check"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button class="button primary full" type="submit">Sign in</button>
        </form>
    </div>
</section>
@endsection
