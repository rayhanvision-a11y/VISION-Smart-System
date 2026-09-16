<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
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
            'theme_preference'   => 'required|in:light,dark,system',
            'locale'             => 'nullable|in:en,bn',
            'timezone'           => 'nullable|timezone',
            'notify_on_assign'   => 'nullable|boolean',
            'notify_on_resolve'  => 'nullable|boolean',
            'notify_on_message'  => 'nullable|boolean',
        ]);

        $validated['notify_on_assign']  = $request->boolean('notify_on_assign');
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
    public function updateTheme(Request $request): \Illuminate\Http\JsonResponse
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
}
