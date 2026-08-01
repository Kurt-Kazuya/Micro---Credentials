<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserCodeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function __construct(protected UserCodeService $userCodeService)
    {
    }

    /**
     * Show the registration form.
     */
    public function show()
    {
        return view('register');
    }

    /**
     * Handle a registration request.
     */
    public function store(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $userCode = $this->userCodeService->generateForRole(3);

        // Create the user
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role_id' => 3, // student role
            'student_id' => $userCode,
            'user_code' => $userCode,
            'is_active' => true,
        ]);

        // Dispatch the registered event
        event(new Registered($user));

        // Log the user in
        auth()->login($user);

        // Redirect to dashboard
        return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome ' . $user->first_name . '!');
    }
}
