<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Notifications\NewUserRegistered;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Middleware is now handled in routes
    }

    /**
     * Show the application's registration form.
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request): RedirectResponse
    {
        // Check rate limiting
        $this->checkTooManyRegistrationAttempts($request);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
            'terms' => ['required', 'accepted'],
        ], [
            'name.regex' => 'The name may only contain letters and spaces.',
            'terms.accepted' => 'You must accept the terms and conditions.',
        ]);

        try {
            // Sanitize input
            $validated['name'] = trim($validated['name']);
            $validated['email'] = strtolower(trim($validated['email']));

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 'pending', // Ensure status is set
            ]);

            $user->assignRole('attendant');

            // Log registration
            logger('New user registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Clear rate limiting on successful registration
            RateLimiter::clear($this->throttleKey($request));

            // Notify admin(s)
            $admins = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })->get();

            foreach ($admins as $admin) {
                try {
                    $admin->notify(new NewUserRegistered($user));
                } catch (\Exception $e) {
                    logger('Failed to send new user notification', [
                        'admin_id' => $admin->id,
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            return redirect('/login')->with('success', 'Registration successful. Your account is pending approval by an administrator.');

        } catch (\Exception $e) {
            // Increment rate limiting on failed registration
            RateLimiter::hit($this->throttleKey($request));

            logger('Registration failed', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Ensure the registration request is not rate limited.
     */
    protected function checkTooManyRegistrationAttempts(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => [
                'Too many registration attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.',
            ],
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return 'register_' . $request->ip();
    }
}
