<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AnotherCustomLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.custom-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect()->intended('/sistem');
        }

        return redirect()->back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    public function logout(Request $request)
    {
        Auth::logout();

        // Optional: If you want to invalidate the session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/sistem/login');  // Redirect to login page after logout
    }
    public function index(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/sistem');
        }

        return redirect('/sistem/login');
         // Ganti dengan view yang sesuai
    }
   
}
