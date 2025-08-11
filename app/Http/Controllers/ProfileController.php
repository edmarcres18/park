<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the authenticated user's profile.
     */
    public function edit()
    {
        $user = Auth::user();
        $view = $user->hasRole('attendant') ? 'profile.attendant.edit' : 'profile.admin.edit';

        return view($view, [
            'user' => $user,
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => ['nullable', 'current_password'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Only update password if provided and current_password validated
        if (!empty($validated['password'])) {
            // If password provided and current_password not present, force validation error
            if (!$request->filled('current_password')) {
                return back()
                    ->withErrors(['current_password' => 'Current password is required to set a new password.'])
                    ->withInput();
            }

            $user->password = $validated['password']; // 'hashed' cast will hash it
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }
}


