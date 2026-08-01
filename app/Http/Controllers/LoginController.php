<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show()
    {
        return view('login');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginValue = $request->input('email');
        $credentials = ['password' => $request->input('password')];

        if (filter_var($loginValue, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $loginValue;
        } else {
            $credentials['username'] = $loginValue;
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($profileData = session('profile_data', [])) {
            $user = Auth::user();
            foreach (['name', 'role', 'phone', 'location', 'about', 'date_of_birth', 'gender', 'education', 'bio', 'email', 'language', 'timezone', 'avatar_url'] as $field) {
                if (array_key_exists($field, $profileData)) {
                    $user->{$field} = $profileData[$field];
                }
            }
            $user->profile_completed = true;
            $user->save();
            session()->forget('profile_data');
        }

        return redirect()->route($this->resolveDashboardRoute(Auth::user()));
    }

    protected function resolveDashboardRoute($user): string
    {
        $roleId = (int) ($user->role_id ?? 3);

        if ($roleId === 1) {
            return 'admin.dashboard';
        }

        if ($roleId === 2) {
            return 'faculty.dashboard';
        }

        return ($user->profile_completed ?? false) ? 'dashboard' : 'profile.show';
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
