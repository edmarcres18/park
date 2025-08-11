@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-8">
    <main class="grid grid-cols-1 md:grid-cols-2 bg-white rounded-2xl shadow-xl ring-1 ring-gray-100 overflow-hidden w-full max-w-5xl">
        <!-- Left Panel -->
        <section class="left-panel relative text-white p-10 md:p-12 flex flex-col items-center justify-center text-center bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-600">
            <div class="absolute inset-0 opacity-20 pointer-events-none" aria-hidden="true"></div>
            <div class="max-w-sm mx-auto">
                <div class="inline-flex items-center justify-center w-24 h-24 md:w-28 md:h-28 rounded-2xl bg-white/90 ring-2 ring-white/60 shadow-xl mb-6">
                    <span class="inline-flex">
                        @if(!empty($siteSettings->brand_logo))
                            <img src="{{ asset('storage/' . $siteSettings->brand_logo) }}" alt="Brand Logo" class="w-20 h-20 md:w-24 md:h-24 object-contain drop-shadow-md">
                        @else
                            <span class="text-blue-700 font-semibold text-xl">Your Brand</span>
                        @endif
                    </span>
                </div>
                <h1 class="text-3xl font-semibold tracking-tight mb-3">New here?</h1>
                <p class="text-white/90 mb-8 leading-relaxed">Sign up and discover a great amount of new opportunities!</p>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center border-2 border-white rounded-full py-2.5 px-10 font-semibold hover:bg-white hover:text-blue-700 transition-colors duration-300">
                    SIGN UP
                </a>
            </div>
        </section>

        <!-- Right Panel -->
        <section class="right-panel p-8 sm:p-10">
            <header class="mb-8">
                <p class="text-sm text-gray-500 mb-1">Welcome back</p>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Login to Your Account</h2>
            </header>

            <form method="POST" action="{{ route('login') }}" id="loginForm" class="grid gap-5">
                @csrf
                <div class="grid gap-2">
                    <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                    <input id="email" type="email" class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 focus:ring-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com" maxlength="255" spellcheck="false" autofocus aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-2">
                    <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                    <input id="password" type="password" class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 focus:ring-red-500 @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password" minlength="8" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 rounded" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="text-gray-700 text-sm">Remember me</span>
                    </label>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-300 disabled:opacity-50" id="loginBtn">
                        SIGN IN
                    </button>
                </div>
            </form>

            <script>
                document.getElementById('loginForm').addEventListener('submit', function(e) {
                    const btn = document.getElementById('loginBtn');
                    btn.disabled = true;
                    btn.textContent = 'SIGNING IN...';

                    // Re-enable button after 3 seconds to prevent indefinite disable
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.textContent = 'SIGN IN';
                    }, 3000);
                });

                // Client-side validation
                document.getElementById('email').addEventListener('blur', function() {
                    const email = this.value;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (email && !emailRegex.test(email)) {
                        this.classList.add('border-red-500');
                    } else {
                        this.classList.remove('border-red-500');
                    }
                });
            </script>
        </section>
    </main>
</div>
@endsection

