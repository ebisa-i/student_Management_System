@extends('layouts.app')

@section('title', ucfirst($role).'s')
@section('content')
<div class="page-heading">
    <div><p class="eyebrow">ADMINISTRATION</p><h1>{{ ucfirst($role) }}s</h1><p class="muted">Manage {{ $role }} accounts and contact details.</p></div>
    <a class="button primary" href="{{ route('admin.users.create', ['role' => $role]) }}">Add {{ $role }}</a>
</div>
<div class="panel table-wrap">
    <table><thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Phone</th><th></th></tr></thead><tbody>
    @forelse($users as $user)
        <tr><td>{{ $user->full_name }}</td><td>{{ $user->username }}</td><td>{{ $user->email ?: '—' }}</td><td>{{ $user->phone ?: '—' }}</td><td class="actions"><div class="account-actions"><a class="button secondary small" href="{{ route('admin.users.edit', $user) }}">Edit profile</a><form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this account?')">@csrf @method('DELETE')<button class="button danger small" type="submit">Delete</button></form></div></td></tr>
    @empty<tr><td colspan="5" class="empty">No {{ $role }} accounts yet.</td></tr>@endforelse
    </tbody></table>
    <div class="pagination">{{ $users->links() }}</div>
</div>
@endsection
