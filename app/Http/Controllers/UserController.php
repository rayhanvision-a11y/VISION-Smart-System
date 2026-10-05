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

    public function index(Request $request)
    {
        $this->adminOnly();

        $designationFilter = trim((string) $request->get('designation', ''));
        $officeFilter = $request->get('pop_office_id');
        $officeIdFilter = trim((string) $request->get('office_id', ''));

        $query = User::withCount([
            'tickets as created_count',
            'assignedTickets as resolved_count' => fn ($q) => $q->where('status', 'resolved'),
        ])->with('popOffice');

        if ($designationFilter !== '') {
            $query->where('designation', $designationFilter);
        }
        if ($officeFilter) {
            $query->where('pop_office_id', $officeFilter);
        }
        if ($officeIdFilter !== '') {
            $query->where('office_id', 'like', '%'.$officeIdFilter.'%');
        }

        $users = $query->paginate(20)->withQueryString();

        $designations = User::whereNotNull('designation')->where('designation', '!=', '')
            ->distinct()->orderBy('designation')->pluck('designation');
        $offices = \App\Models\PopOffice::where('is_active', true)->orderBy('name')->get();

        return view('users.index', compact('users', 'designations', 'offices', 'designationFilter', 'officeFilter', 'officeIdFilter'));
    }

    public function create()
    {
        $this->adminOnly();

        $designations = User::whereNotNull('designation')->where('designation', '!=', '')
            ->distinct()->orderBy('designation')->pluck('designation');
        $offices = \App\Models\PopOffice::where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('designations', 'offices'));
    }

    public function store(Request $request)
    {
        $this->adminOnly();

        $authUser = auth()->user();

        // Determine allowed roles based on actor
        $allowedRoles = $authUser->isSuperAdmin()
            ? 'required|in:super_admin,admin,noc,supervisor,senior_supervisor,call_center,reseller,technician'
            : 'required|in:admin,noc,supervisor,senior_supervisor,call_center,reseller,technician';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => $allowedRoles,
            'designation' => 'nullable|string|max:120',
            'office_id' => 'nullable|string|max:60|unique:users,office_id',
            'pop_office_id' => 'nullable|exists:pop_offices,id',
            'team' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|min:8',
        ]);

        $hasPassword = ! empty($validated['password']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'] ?? Str::random(32)),
            'role' => $validated['role'],
            'designation' => $validated['designation'] ?? null,
            'office_id' => $validated['office_id'] ?? null,
            'pop_office_id' => $validated['pop_office_id'] ?? null,
            'team' => $validated['team'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        try {
            Mail::to($user->email)->send(new WelcomeUser($user));
        } catch (\Exception $e) {
            \Log::warning('Welcome email failed: '.$e->getMessage());
        }

        // Only send reset link when admin didn't set a password upfront
        if (! $hasPassword) {
            try {
                Password::sendResetLink(['email' => $user->email]);
            } catch (\Exception $e) {
                \Log::warning('Password reset link email failed: '.$e->getMessage());
            }
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(string $id)
    {
        $this->adminOnly();
        $editUser = User::findOrFail($id);

        $designations = User::whereNotNull('designation')->where('designation', '!=', '')
            ->distinct()->orderBy('designation')->pluck('designation');
        $offices = \App\Models\PopOffice::where('is_active', true)->orderBy('name')->get();

        return view('users.edit', compact('editUser', 'designations', 'offices'));
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
            ? 'required|in:super_admin,admin,noc,supervisor,senior_supervisor,call_center,reseller,technician'
            : 'required|in:noc,supervisor,senior_supervisor,call_center,reseller,technician';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$editUser->id,
            'role' => $allowedRoles,
            'designation' => 'nullable|string|max:120',
            'office_id' => 'nullable|string|max:60|unique:users,office_id,'.$editUser->id,
            'pop_office_id' => 'nullable|exists:pop_offices,id',
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
            'designation' => $validated['designation'] ?? null,
            'office_id' => $validated['office_id'] ?? null,
            'pop_office_id' => $validated['pop_office_id'] ?? null,
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
