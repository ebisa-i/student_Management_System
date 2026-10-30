@extends('layouts.app')

@section('title', 'Edit '.ucfirst($user->role))
@section('content')
<div class="page-heading"><div><p class="eyebrow">ADMINISTRATION</p><h1>Edit {{ ucfirst($user->role) }} profile</h1></div><a class="button secondary" href="{{ route('admin.users.index', ['role' => $user->role]) }}">Back to {{ $user->role }}s</a></div>
<form method="POST" action="{{ route('admin.users.update', $user) }}" class="panel form-grid">
    @csrf @method('PUT')
    <label>Full name<input name="full_name" value="{{ old('full_name', $user->full_name) }}" required maxlength="100"></label>
    <label>Username<input name="username" value="{{ old('username', $user->username) }}" required maxlength="50"></label>
    <label>Email<input type="email" name="email" value="{{ old('email', $user->email) }}"></label>
    <label>Phone<input name="phone" value="{{ old('phone', $user->phone) }}"></label>
    <label>Gender<select name="gender" required><option value="">Select gender</option><option value="female" @selected(old('gender', $user->gender) === 'female')>Female</option><option value="male" @selected(old('gender', $user->gender) === 'male')>Male</option></select></label>
    @if($user->role === 'teacher')<label>Years of experience<input type="number" name="experience" min="0" max="80" value="{{ old('experience', $user->experience) }}"></label>@endif
    <label class="span-2">Address<input name="address" value="{{ old('address', $user->address) }}"></label>
    <div class="span-2"><button class="button primary" type="submit">Save profile</button></div>
</form>
@endsection
