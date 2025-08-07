@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="flex flex-col md:flex-row bg-white rounded-lg shadow-lg overflow-hidden max-w-4xl w-full">
        <!-- Left Panel -->
        <div class="left-panel text-white w-full md:w-1/2 p-12 flex flex-col justify-center items-center text-center">
            <h1 class="text-3xl font-bold mb-4">New here?</h1>
            <p class="mb-8">Sign up and discover a great amount of new opportunities!</p>
            <a href="{{ route('register') }}" class="border-2 border-white rounded-full py-2 px-12 font-semibold hover:bg-white hover:text-blue-700 transition-colors duration-300">SIGN UP</a>
        </div>

        <!-- Right Panel -->
        <div class="right-panel w-full md:w-1/2 p-12">
            <h2 class="text-3xl font-bold mb-2">Login to Your Account</h2>


            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                <div class="mb-4">
                    <input id="email" type="email" class="w-full px-4 py-3 rounded-lg bg-gray-200 border focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Email" maxlength="255">
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <input id="password" type="password" class="w-full px-4 py-3 rounded-lg bg-gray-200 border focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror" name="password" required autocomplete="current-password" placeholder="Password" minlength="8">
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span class="ml-2 text-gray-700">Remember me</span>
                    </label>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-full transition-colors duration-300 disabled:opacity-50" id="loginBtn">
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
        </div>
    </div>
</div>
@endsection

