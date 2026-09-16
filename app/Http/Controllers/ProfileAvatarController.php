<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileAvatarController extends Controller
{
    public function update(Request $request)
    {
        $request->validate(['avatar' => 'required|image|max:2048']);

        $user = auth()->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return back()->with('success', 'Profile photo updated.');
    }
}
