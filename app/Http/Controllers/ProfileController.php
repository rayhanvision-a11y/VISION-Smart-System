<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the user's preferences (theme, language, timezone, notifications).
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme_preference' => 'required|in:light,dark,system',
            'locale' => 'nullable|in:en,bn',
            'timezone' => 'nullable|timezone',
            'notify_on_assign' => 'nullable|boolean',
            'notify_on_resolve' => 'nullable|boolean',
            'notify_on_message' => 'nullable|boolean',
        ]);

        $validated['notify_on_assign'] = $request->boolean('notify_on_assign');
        $validated['notify_on_resolve'] = $request->boolean('notify_on_resolve');
        $validated['notify_on_message'] = $request->boolean('notify_on_message');

        $request->user()->update($validated);

        if ($validated['locale']) {
            session(['locale' => $validated['locale']]);
        }

        return Redirect::route('profile.edit')->with('status', 'preferences-updated');
    }

    /**
     * Update the user's theme preference via the quick toggle.
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme' => 'required|in:light,dark,system',
        ]);

        $request->user()->update(['theme_preference' => $validated['theme']]);

        return response()->json(['status' => 'ok']);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Return user profile details and active ticket statistics (JSON for popup card).
     */
    public function cardData(Request $request, ?User $user = null): JsonResponse
    {
        $targetUser = $user ?? $request->user();
        if (! $targetUser) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $activeCount = $targetUser->activeTicketsCount();
        $totalCount = $targetUser->totalTicketsCount();
        $resolvedCount = $targetUser->resolvedTicketsCount();
        $systemActive = $request->user()->isAdmin()
            ? Ticket::whereNotIn('status', ['resolved', 'closed'])->count()
            : null;

        if ($targetUser->isReseller()) {
            $ticketsUrl = route('tickets.index', ['created_by' => $targetUser->id]);
            $activeTicketsUrl = route('tickets.index', ['created_by' => $targetUser->id, 'status' => 'active']);
        } elseif ($targetUser->isAdmin()) {
            $ticketsUrl = route('tickets.index');
            $activeTicketsUrl = route('tickets.index', ['status' => 'active']);
        } else {
            $isMe = $request->user()->id === $targetUser->id;
            $ticketsUrl = $isMe
                ? route('tickets.index', ['assigned' => 'me'])
                : route('tickets.index', ['assigned_to' => $targetUser->id]);
            $activeTicketsUrl = $isMe
                ? route('tickets.index', ['assigned' => 'me', 'status' => 'active'])
                : route('tickets.index', ['assigned_to' => $targetUser->id, 'status' => 'active']);
        }

        return response()->json([
            'id' => $targetUser->id,
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'phone' => $targetUser->phone ?? '',
            'role' => $targetUser->role,
            'role_label' => strtoupper(str_replace('_', ' ', $targetUser->role)),
            'team' => $targetUser->team ?? '',
            'avatar_url' => $targetUser->avatarUrl(),
            'current_shift' => $targetUser->current_shift ?? 'unassigned',
            'current_shift_label' => User::SHIFTS[$targetUser->current_shift ?? 'unassigned'] ?? ($targetUser->current_shift ?? 'Unassigned'),
            'is_on_duty' => $targetUser->isOnDuty(),
            'active_tickets' => $activeCount,
            'resolved_tickets' => $resolvedCount,
            'total_tickets' => $totalCount,
            'system_active_tickets' => $systemActive,
            'tickets_url' => $ticketsUrl,
            'active_tickets_url' => $activeTicketsUrl,
            'is_me' => $request->user()->id === $targetUser->id,
            'profile_url' => route('profile.edit'),
        ]);
    }
}
