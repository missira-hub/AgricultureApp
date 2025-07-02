<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $role = $this->determineRole($request->email); // Or some logic you define

   $user = User::create([
    'name' => $request->name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'role' => $this->assignRoleByEmail($request->email),
]);


    event(new Registered($user));

    Auth::login($user);

    return redirect(route('dashboard'));
}

private function determineRole($email)
{
    // Example: domain-based role logic
    if (str_ends_with($email, '@agrichome-admin.com')) return 'admin';
    if (str_ends_with($email, '@farmer.com')) return 'farmer';
    return 'consumer';
}
private function assignRoleByEmail($email)
{
    return match (true) {
        str_ends_with($email, '@admin.com') => 'admin',
        str_ends_with($email, '@farmer.com') => 'farmer',
        default => 'consumer',
    };
}

}