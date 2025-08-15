<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileApiController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:attendant']);
    }

    /**
     * Get authenticated attendant profile.
     */
    public function show(): JsonResponse
    {
        $user = Auth::user();
        $user->load('roles');

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
                'roles' => $user->roles->pluck('name'),
            ],
        ]);
    }

    /**
     * Update authenticated attendant profile (name, email, and optional password change with current_password check).
     */
    public function update(Request $request): JsonResponse
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
            if (!$request->filled('current_password')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Current password is required to set a new password.'
                ], 422);
            }
            $user->password = $validated['password']; // 'hashed' cast will hash it
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status,
            ],
        ]);
    }
}


