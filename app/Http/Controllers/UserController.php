<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private function adminOnly()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }
    }

    public function index()
    {
        $this->adminOnly();

        $users = User::withCount([
            'tickets as created_count',
            'assignedTickets as resolved_count' => fn ($q) => $q->where('status', 'resolved'),
        ])->paginate(20);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $this->adminOnly();

        return view('users.create');
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $authUser = auth()->user();

        // Determine allowed roles based on actor
        $allowedRoles = $authUser->isSuperAdmin()
            ? 'required|in:super_admin,admin,noc,supervisor,senior_supervisor,call_center,reseller'
            : 'required|in:admin,noc,supervisor,senior_supervisor,call_center,reseller';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => $allowedRoles,
            'team' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(Str::random(32)), // temporary; user sets via reset link
            'role' => $validated['role'],
            'team' => $validated['team'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        try {
            Mail::to($user->email)->send(new WelcomeUser($user));
        } catch (\Exception $e) {
            \Log::warning('Welcome email failed: '.$e->getMessage());
        }

        try {
            Password::sendResetLink(['email' => $user->email]);
        } catch (\Exception $e) {
            \Log::warning('Password reset link email failed: '.$e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(string $id)
    {
        $this->adminOnly();
        $editUser = User::findOrFail($id);

        return view('users.edit', compact('editUser'));
    }

    public function update(Request $request, string $id)
    {
        $this->adminOnly();

        $authUser = auth()->user();
        $editUser = User::findOrFail($id);

        // Non-super-admins cannot edit super_admin accounts
        if ($editUser->isSuperAdmin() && ! $authUser->isSuperAdmin()) {
            abort(403, 'Only super admins can edit super admin accounts.');
        }

        $allowedRoles = $authUser->isSuperAdmin()
            ? 'required|in:super_admin,admin,noc,supervisor,senior_supervisor,call_center,reseller'
            : 'required|in:noc,supervisor,senior_supervisor,call_center,reseller';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$editUser->id,
            'role' => $allowedRoles,
            'team' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'team' => $validated['team'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'phone' => $validated['phone'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($editUser->avatar && \Storage::disk('public')->exists($editUser->avatar)) {
                \Storage::disk('public')->delete($editUser->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $editUser->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(string $id)
    {
        $authUser = auth()->user();
        if (! $authUser->isSuperAdmin()) {
            abort(403);
        }

        $editUser = User::findOrFail($id);

        if ($editUser->id === $authUser->id) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        // Delete avatar from storage
        if ($editUser->avatar && \Storage::disk('public')->exists($editUser->avatar)) {
            \Storage::disk('public')->delete($editUser->avatar);
        }

        $editUser->delete();

        return redirect()->route('users.index')->with('success', 'User account deleted.');
    }

    public function resetPassword(string $id)
    {
        $this->adminOnly();

        $editUser = User::findOrFail($id);

        try {
            Password::sendResetLink(['email' => $editUser->email]);
        } catch (\Exception $e) {
            \Log::warning('Password reset link email failed: '.$e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'Password reset link sent to '.$editUser->email.'.');
    }
}
