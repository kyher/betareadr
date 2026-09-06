<?php

namespace App\Http\Controllers;

use App\Models\Administrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthenticationController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        return Auth::guard(Administrator::AUTH_GUARD)->check()
            ? redirect()->route('admin.dashboard')
            : Inertia::render('admin/Login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);
        $credentials['email'] = Str::lower($credentials['email']);
        $key = 'admin-login:'.hash('sha256', $credentials['email'].'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($key, 5) || ! Auth::guard(Administrator::AUTH_GUARD)->attempt($credentials)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard(Administrator::AUTH_GUARD)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
