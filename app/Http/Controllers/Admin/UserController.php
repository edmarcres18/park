<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['roles', 'branch'])->get();

        return view('admin.users.index', compact('users'));
    }

    public function rejected()
    {
        $users = User::with(['roles', 'branch'])->whereHas('roles', function ($query) {
            $query->where('name', 'attendant')
            ->where('status', 'rejected');
        })->get();

        return view('admin.users.rejected', compact('users'));
    }

    public function pending()
    {
        $users = User::with(['roles', 'branch'])->whereHas('roles', function ($query) {
            $query->where('name', 'attendant')
            ->where('status', 'pending');
        })->get();

        return view('admin.users.pending', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['roles', 'branch']);
        return view('admin.users.show', compact('user'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        return view('admin.users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status' => ['required', 'in:active,pending,rejected'],
            'role' => ['required', 'in:admin,attendant'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'status' => $request->status,
                'branch_id' => $request->branch_id,
            ]);

            // Assign the selected role
            $user->assignRole($request->role);


            return redirect()->route('admin.users.index')
                ->with('success', "User '{$user->name}' has been created successfully as {$request->role}.");
        } catch (\Exception $e) {
            \Log::error('User creation failed: ' . $e->getMessage(), [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user. Please try again or contact support.');
        }
    }

    public function edit(User $user)
    {
        $branches = Branch::orderBy('name')->get();
        $user->load(['roles', 'branch']);
        return view('admin.users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'status' => ['required', 'in:active,pending,rejected'],
            'role' => ['required', 'in:admin,attendant'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'status' => $request->status,
                'branch_id' => $request->branch_id,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $user->update($updateData);

            // Update role if changed
            $currentRole = $user->roles->first();
            if (!$currentRole || $currentRole->name !== $request->role) {
                $user->syncRoles([$request->role]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', "User '{$user->name}' has been updated successfully.");
        } catch (\Exception $e) {
            \Log::error('User update failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user. Please try again or contact support.');
        }
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'in:active,rejected'],
        ]);

        $oldStatus = $user->status;
        $user->update(['status' => $request->status]);


        return redirect()->back()->with('status', 'User status updated successfully.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        User::whereIn('id', $request->user_ids)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'attendant');
            })
            ->update(['status' => 'active']);

        return redirect()->back()->with('status', count($request->user_ids) . ' users approved successfully.');
    }

    public function bulkReject(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        User::whereIn('id', $request->user_ids)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'attendant');
            })
            ->update(['status' => 'rejected']);

        return redirect()->back()->with('status', count($request->user_ids) . ' users rejected successfully.');
    }

    public function approveAll()
    {
        $pendingUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'attendant');
        })->where('status', 'pending')->get();

        $pendingUsers->each(function ($user) {
            $user->update(['status' => 'active']);
        });

        return redirect()->back()->with('status', $pendingUsers->count() . ' users approved successfully.');
    }

    public function rejectAll()
    {
        $pendingUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'attendant');
        })->where('status', 'pending')->get();

        $pendingUsers->each(function ($user) {
            $user->update(['status' => 'rejected']);
        });

        return redirect()->back()->with('status', $pendingUsers->count() . ' users rejected successfully.');
    }
        public function delete($userId)
    {
        try {
            // Find the user manually to handle cases where route model binding fails
            $user = User::findOrFail($userId);

            // Log the deletion attempt for debugging
            \Log::info('Delete user attempt', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'Unknown'
            ]);

            // Prevent admin from deleting themselves
            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'You cannot delete your own account.');
            }

            // Check if user has admin role
            if ($user->hasRole('admin')) {
                return redirect()->back()->with('error', 'Admin users cannot be deleted through this interface.');
            }

            // Store user info for logging
            $userName = $user->name;
            $userEmail = $user->email;

            // Delete related data
            $user->notifications()->delete(); // Delete notifications sent by this user
            $user->roles()->detach(); // Remove role assignments
            $user->permissions()->detach(); // Remove permission assignments

            // Delete the user
            $user->delete();


            \Log::info('User deleted successfully', [
                'user_id' => $user->id,
                'user_name' => $userName,
                'admin_id' => auth()->id()
            ]);

            return redirect()->back()->with('success', "User '{$userName}' ({$userEmail}) has been permanently deleted.");
        } catch (\Exception $e) {
            \Log::error('User deletion failed: ' . $e->getMessage(), [
                'user_id' => $userId ?? 'unknown',
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to delete user. Please try again or contact support.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        try {
            $usersToDelete = User::whereIn('id', $request->user_ids)
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'attendant');
                })
                ->where('id', '!=', auth()->id()) // Prevent self-deletion
                ->get();

            $deletedCount = 0;
            $deletedUsers = [];

            foreach ($usersToDelete as $user) {
                // Delete related data
                $user->notifications()->delete();
                $user->roles()->detach();
                $user->permissions()->detach();

                $deletedUsers[] = $user->name;
                $user->delete();
                $deletedCount++;
            }

            if ($deletedCount > 0) {
                return redirect()->back()->with('success', "Successfully deleted {$deletedCount} user(s): " . implode(', ', $deletedUsers));
            } else {
                return redirect()->back()->with('warning', 'No eligible users were found for deletion.');
            }
        } catch (\Exception $e) {
            \Log::error('Bulk user deletion failed: ' . $e->getMessage(), [
                'user_ids' => $request->user_ids,
                'admin_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Failed to delete users. Please try again or contact support.');
        }
    }
}
