<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register', ['hospitals' => \App\Models\Hospital::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug'])]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:patient,driver,hospital_admin,super_admin'],
            'phone' => ['nullable', 'string', 'max:32'],
            'hospital_id' => ['nullable', 'integer', 'exists:hospitals,id'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'hospital_id' => $validated['hospital_id'] ?? null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard.or.home');
    }
}
