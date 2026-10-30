@extends('layouts.app')

@section('title', 'Add '.ucfirst($role))
@section('content')
<div class="page-heading"><div><p class="eyebrow">ADMINISTRATION</p><h1>Add {{ $role }}</h1></div><a class="button secondary" href="{{ route('admin.users.index', ['role' => $role]) }}">Back to {{ $role }}s</a></div>
<form method="POST" action="{{ route('admin.users.store') }}" class="panel form-grid">
    @csrf<input type="hidden" name="role" value="{{ $role }}">
    <label>Full name<input name="full_name" value="{{ old('full_name') }}" required maxlength="100"></label>
    <label>Username<input name="username" value="{{ old('username') }}" required maxlength="50"></label>
    <label>Email<input type="email" name="email" value="{{ old('email') }}"></label>
    <label>Phone<input name="phone" value="{{ old('phone') }}"></label>
    <label>Gender<select name="gender" required><option value="">Select gender</option><option value="female" @selected(old('gender') === 'female')>Female</option><option value="male" @selected(old('gender') === 'male')>Male</option></select></label>
    @if($role === 'teacher')<label>Years of experience<input type="number" name="experience" min="0" max="80" value="{{ old('experience') }}"></label>@endif
    <label class="span-2">Address<input name="address" value="{{ old('address') }}"></label>
    <label>Password<input type="password" name="password" required minlength="8"></label>
    <label>Confirm password<input type="password" name="password_confirmation" required minlength="8"></label>
    <div class="span-2"><button class="button primary" type="submit">Create account</button></div>
</form>
@endsection
