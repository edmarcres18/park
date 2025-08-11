@extends('layouts.attendant')

@section('title', 'My Profile')
@section('subtitle', 'Update your profile information')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white/80 backdrop-blur-xl border border-white/60 rounded-2xl shadow-xl p-6">
        <div class="flex items-center gap-4 mb-4">
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=10b981&color=fff&rounded=true" alt="Avatar" class="w-12 h-12 rounded-full" />
            <div>
                <h2 class="text-xl font-semibold text-slate-800">Profile Information</h2>
                <p class="text-sm text-slate-600">Keep your account details up to date</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="name">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                           class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white/70" />
                    @error('name')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                           class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white/70" />
                    @error('email')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="pt-2">
                <h3 class="text-sm font-semibold text-slate-700 mb-2">Account Security</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="current_password">Current Password</label>
                        <input id="current_password" name="current_password" type="password" autocomplete="current-password"
                               class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white/70" />
                        @error('current_password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-slate-500 mt-1">Required only when changing your password</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700" for="password">New Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white/70" />
                        @error('password')
                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700" for="password_confirmation">Confirm New Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                               class="mt-1 w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-green-500 bg-white/70" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ url()->previous() }}" class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-lg text-white bg-green-600 hover:bg-green-700 transition">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection


