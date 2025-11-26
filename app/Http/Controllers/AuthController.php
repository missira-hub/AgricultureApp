<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user. All users are defaulted to 'consumer'.
     */
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
            // ❌ Removed role from validation
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role' => 'consumer', // ✅ Default role assignment
        ]);

        $token = $user->createToken('apptoken')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * Login an existing user.
     */
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $token = $user->createToken('apptoken')->plainTextToken;

        $redirectUrl = match ($user->role) {
            'admin' => '/admin/dashboard',
            'farmer' => '/farmer/dashboard',
            'consumer' => '/consumer/dashboard',
            default => '/'
        };

        return response([
            'user' => $user,
            'role' => $user->role,
            'redirect_url' => $redirectUrl,
            'token' => $token
        ], 200);
    }

    /**
     * Update the profile of the currently authenticated user.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:6|confirmed',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->fresh(),
        ]);
    }
    public function logout(Request $request)
{
    // Revoke the current token
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
}
public function logoutAll(Request $request)
{
    $request->user()->tokens()->delete(); // Deletes all tokens for user

    return response()->json([
        'message' => 'Logged out from all sessions'
    ]);
}
// In AuthController.php
public function profile(Request $request)
{
    $user = $request->user();
    // Add extra relations if needed, e.g. $user->load('profileImage');
    return response()->json($user);
}

}
