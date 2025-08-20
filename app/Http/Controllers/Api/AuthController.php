<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            activity('authentication')
                ->withProperties([
                    'action' => 'api_login_failed',
                    'email' => $request->email,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('API login failed');
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->status !== 'active') {
            activity('authentication')
                ->causedBy($user)
                ->withProperties([
                    'action' => 'api_login_blocked',
                    'reason' => 'inactive_status',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('API login blocked due to inactive status');
            return response()->json(['message' => 'Your account is not active.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        activity('authentication')
            ->causedBy($user)
            ->withProperties([
                'action' => 'api_login_success',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('User logged in via API');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            activity('authentication')
                ->withProperties([
                    'action' => 'api_register_validation_failed',
                    'errors' => $validator->errors()->toArray(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('API registration validation failed');
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'status'   => 'pending', // Mark as pending until approved by admin
        ]);

        $user->assignRole('attendant');

        activity('authentication')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'action' => 'api_register_success',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('User registered via API');

        $adminUsers = User::role('admin')->get();

        if ($adminUsers->count() > 0) {
            Notification::send($adminUsers, new NewUserRegistered($user));
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration successful. Your account is pending approval.',
            'data'    => [
                'user_id' => $user->id,
                'email'   => $user->email,
                'role'    => 'attendant',
                'status'  => 'pending'
            ]
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        activity('authentication')
            ->causedBy($request->user())
            ->withProperties([
                'action' => 'api_logout',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('User logged out via API');

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        activity('authentication')
            ->causedBy($user)
            ->withProperties([
                'action' => 'api_me',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('Fetched current user via API');

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'roles' => $user->roles->pluck('name'),
        ]);
    }
}
