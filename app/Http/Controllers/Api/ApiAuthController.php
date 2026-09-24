<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ApiAuthController extends Controller
{
    /**
     * Mobile App Login.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password',
            ], 401);
        }

        if (isset($user->is_active) && ! $user->is_active) {
            return response()->json([
                'message' => 'Account is deactivated. Please contact support.',
            ], 403);
        }

        $deviceName = $request->input('device_name', 'mobile_app');
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'team' => $user->team,
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatarUrl(),
            ],
        ]);
    }

    /**
     * Logout & Revoke API Tokens.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get Current Authenticated User Profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'team' => $user->team,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatarUrl(),
                'locale' => $user->locale,
                'fcm_token' => $user->fcm_token,
            ],
        ]);
    }

    /**
     * Update basic profile fields.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'nullable|string|max:32',
        ]);
        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'team' => $user->team,
                'phone' => $user->phone,
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatarUrl(),
            ],
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($validated['new_password'])]);

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * Upload user avatar image (multipart).
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $user = $request->user();

        // Delete previous local avatar if exists
        if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
            $oldPath = public_path('storage/'.$user->avatar);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file = $request->file('avatar');
        $filename = 'user_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
        $file->storeAs('avatars', $filename, 'public');
        $user->update(['avatar' => 'avatars/'.$filename]);

        return response()->json([
            'message' => 'Avatar updated successfully',
            'avatar' => $user->avatar,
            'avatar_url' => $user->fresh()->avatarUrl(),
        ]);
    }

    /**
     * Update FCM Token for Push Notifications.
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = $request->user();
        $user->update([
            'fcm_token' => $request->input('fcm_token'),
        ]);

        return response()->json([
            'message' => 'FCM Token updated successfully',
        ]);
    }
}
