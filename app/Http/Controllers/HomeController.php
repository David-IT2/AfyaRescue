<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Redirect to role-appropriate dashboard or home.
     */
    public function __invoke()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        if ($user->hasRole('hospital_admin', 'super_admin')) {
            return redirect()->route('hospital.dashboard');
        }
        if ($user->hasRole('patient')) {
            return redirect()->route('emergency.create');
        }
        if ($user->hasRole('driver')) {
            return redirect()->route('driver.dashboard');
        }
        return redirect('/');
    }
}
