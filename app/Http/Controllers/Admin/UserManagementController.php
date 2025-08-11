<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserManagementController extends Controller
{
    private function authorizeAdmin()
{
    if (auth()->user()->role !== 'admin') {
        abort(403, 'Unauthorized. Admin access only.');
    }
}

  public function index()
{
    $this->authorizeAdmin();

    try {
        // Make sure these fields exist in your users table
        $users = User::select('id', 'name', 'email', 'role')->get();

        return response()->json($users, 200);
    } catch (\Exception $e) {
        \Log::error('Error fetching users in UserManagementController@index: ' . $e->getMessage());
        return response()->json(['error' => 'Something went wrong while fetching users.'], 500);
    }
}



    public function upgradeToFarmer($id)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);
        $user->role = 'farmer';
        $user->save();
        return response()->json(['message' => 'User upgraded to farmer']);
    }

    public function downgradeToConsumer($id)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);
        $user->role = 'consumer';
        $user->save();
        return response()->json(['message' => 'User downgraded to consumer']);
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();

        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
