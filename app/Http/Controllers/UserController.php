<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role', 'student');
        abort_unless(in_array($role, ['student', 'teacher'], true), 404);
        $users = User::where('role', $role)->orderBy('full_name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('role', 'users'));
    }

    public function create(Request $request): View
    {
        $role = $request->query('role', 'student');
        abort_unless(in_array($role, ['student', 'teacher'], true), 404);

        return view('admin.users.create', compact('role'));
    }

    public function edit(User $user): View
    {
        abort_unless(in_array($user->role, ['student', 'teacher'], true), 404);

        return view('admin.users.edit', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['student', 'teacher'])],
            'full_name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', Rule::in(['female', 'male'])],
            'address' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create($data);

        return redirect()->route('admin.users.index', ['role' => $data['role']])->with('status', ucfirst($data['role']).' account created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['student', 'teacher'], true), 404);

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', Rule::in(['female', 'male'])],
            'address' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'integer', 'min:0', 'max:80'],
        ]);

        $user->update($data);

        return redirect()->route('admin.users.index', ['role' => $user->role])->with('status', 'Account profile updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['student', 'teacher'], true), 404);
        $role = $user->role;
        $user->delete();

        return redirect()->route('admin.users.index', ['role' => $role])->with('status', 'Account deleted.');
    }
}
