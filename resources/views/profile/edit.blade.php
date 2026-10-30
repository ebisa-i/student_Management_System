@extends('layouts.app')

@section('title', 'My profile')
@section('content')
<div class="page-heading"><div><p class="eyebrow">ACCOUNT</p><h1>My profile</h1><p class="muted">Update your contact details or change your password.</p></div></div>
<form method="POST" action="{{ route('profile.update') }}" class="panel form-grid">
    @csrf @method('PUT')
    <label>Full name<input value="{{ auth()->user()->full_name }}" disabled></label>
    <label>Username<input value="{{ auth()->user()->username }}" disabled></label>
    <label>Email<input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"></label>
    <label>Phone<input name="phone" value="{{ old('phone', auth()->user()->phone) }}"></label>
    <label class="span-2">Address<input name="address" value="{{ old('address', auth()->user()->address) }}"></label>
    <label>New password<input type="password" name="password" minlength="8" autocomplete="new-password"></label>
    <label>Confirm new password<input type="password" name="password_confirmation" minlength="8" autocomplete="new-password"></label>
    <div class="span-2"><button class="button primary" type="submit">Save profile</button></div>
</form>
@endsection
