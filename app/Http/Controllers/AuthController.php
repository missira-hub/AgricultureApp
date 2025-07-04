<?php


namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth; // 


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'role' => 'required|in:farmer,consumer,admin'
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'role' => $fields['role']
        ]);

        $token = $user->createToken('apptoken')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token
        ], 201);
    }

   public function login(Request $request)
{
    $fields = $request->validate([
        'email' => 'required|string',
        'password' => 'required|string'
    ]);

    $user = User::where('email', $fields['email'])->first();

    if (!$user || !Hash::check($fields['password'], $user->password)) {
        return response([
            'message' => 'Invalid credentials'
        ], 401);
    }

    $token = $user->createToken('apptoken')->plainTextToken;

    // Define redirect URL
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

   public function updateProfile(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
        'password' => 'sometimes|required|string|min:6|confirmed',
        // Add other fields like 'phone', 'address' if needed
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

}


