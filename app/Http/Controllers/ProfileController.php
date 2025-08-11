<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the authenticated user's profile.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'avatar_url' => $user->avatar
                ? asset('storage/' . $user->avatar)
                : null,
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
public function updateProfile(Request $request)
{
    $user = $request->user();
logger($request->hasFile('avatar') ? 'Avatar file exists' : 'No avatar file uploaded');


    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'avatar' => 'nullable|image|max:1000000',
        'password' => 'nullable|string|min:6|confirmed',
    ]);

    $user->name = $request->name;
    $user->email = $request->email;

    if ($request->hasFile('avatar')) {
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
    }

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
    ]);
}

}
