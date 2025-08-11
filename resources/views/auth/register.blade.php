@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4 py-8">
    <main class="grid grid-cols-1 md:grid-cols-2 bg-white rounded-2xl shadow-xl ring-1 ring-gray-100 overflow-hidden w-full max-w-5xl">
        <!-- Left Panel -->
        <section class="left-panel relative text-white p-10 md:p-12 flex flex-col justify-center items-center text-center bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-600">
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
                <h1 class="text-3xl font-semibold tracking-tight mb-3">Already Signed up?</h1>
                <p class="text-white/90 mb-8 leading-relaxed">Log in to your account so you can continue building and editing your onboarding flows.</p>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center border-2 border-white rounded-full py-2.5 px-10 font-semibold hover:bg-white hover:text-blue-700 transition-colors duration-300">LOG IN</a>
            </div>
        </section>

        <!-- Right Panel -->
        <section class="right-panel p-8 sm:p-10">
            <header class="mb-8">
                <p class="text-sm text-gray-500 mb-1">Create account</p>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight">Sign Up for an Account</h2>
                <p class="text-gray-600 mt-2">Let's get you all set up so you can start creating your first onboarding experience.</p>
            </header>

            <form method="POST" action="{{ route('register') }}" id="registerForm" class="grid gap-5">
                @csrf
                <div class="grid gap-2">
                    <label for="name" class="text-sm font-medium text-gray-700">Name</label>
                    <input id="name" type="text" class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 focus:ring-red-500 @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Your name" maxlength="255" minlength="2" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                    @error('name')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div id="name-error" class="text-red-500 text-xs mt-1 hidden">Name must be at least 2 characters long.</div>
                </div>

                <div class="grid gap-2">
                    <label for="email" class="text-sm font-medium text-gray-700">Email</label>
                    <input id="email" type="email" class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 focus:ring-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email address" maxlength="255" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                    @error('email')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div id="email-error" class="text-red-500 text-xs mt-1 hidden">Please enter a valid email address.</div>
                </div>

                <div class="grid gap-2">
                    <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                    <input id="password" type="password" class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 focus:ring-red-500 @enderror" name="password" required autocomplete="new-password" placeholder="Enter a strong password" minlength="8" aria-describedby="password-strength" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                    @error('password')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <div id="password-strength" class="text-sm mt-2">
                        <div class="text-gray-600">Password must contain:</div>
                        <div id="length-check" class="text-red-500">✗ At least 8 characters</div>
                        <div id="uppercase-check" class="text-red-500">✗ One uppercase letter</div>
                        <div id="lowercase-check" class="text-red-500">✗ One lowercase letter</div>
                        <div id="number-check" class="text-red-500">✗ One number</div>
                    </div>
                </div>

                <div class="grid gap-2">
                    <label for="password-confirm" class="text-sm font-medium text-gray-700">Confirm Password</label>
                    <input id="password-confirm" type="password" class="w-full px-4 py-3 rounded-lg bg-gray-100 border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password" minlength="8">
                    <div id="password-match-error" class="text-red-500 text-xs mt-1 hidden">Passwords do not match.</div>
                </div>

                <div class="grid gap-2">
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 rounded" name="terms" id="terms" required>
                        <span class="text-gray-700 text-sm">I accept the <a href="#" class="text-blue-600 hover:underline" onclick="alert('Terms and Conditions would be displayed here'); return false;">Terms & Conditions</a></span>
                    </label>
                    <div id="terms-error" class="text-red-500 text-xs mt-1 hidden">You must accept the terms and conditions.</div>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-300 disabled:opacity-50" id="registerBtn">
                        SIGN UP
                    </button>
                </div>
            </form>

            <script>
                // Password strength validation
                document.getElementById('password').addEventListener('input', function() {
                    const password = this.value;
                    const lengthCheck = document.getElementById('length-check');
                    const uppercaseCheck = document.getElementById('uppercase-check');
                    const lowercaseCheck = document.getElementById('lowercase-check');
                    const numberCheck = document.getElementById('number-check');

                    // Length check
                    if (password.length >= 8) {
                        lengthCheck.className = 'text-green-500';
                        lengthCheck.textContent = '✓ At least 8 characters';
                    } else {
                        lengthCheck.className = 'text-red-500';
                        lengthCheck.textContent = '✗ At least 8 characters';
                    }

                    // Uppercase check
                    if (/[A-Z]/.test(password)) {
                        uppercaseCheck.className = 'text-green-500';
                        uppercaseCheck.textContent = '✓ One uppercase letter';
                    } else {
                        uppercaseCheck.className = 'text-red-500';
                        uppercaseCheck.textContent = '✗ One uppercase letter';
                    }

                    // Lowercase check
                    if (/[a-z]/.test(password)) {
                        lowercaseCheck.className = 'text-green-500';
                        lowercaseCheck.textContent = '✓ One lowercase letter';
                    } else {
                        lowercaseCheck.className = 'text-red-500';
                        lowercaseCheck.textContent = '✗ One lowercase letter';
                    }

                    // Number check
                    if (/[0-9]/.test(password)) {
                        numberCheck.className = 'text-green-500';
                        numberCheck.textContent = '✓ One number';
                    } else {
                        numberCheck.className = 'text-red-500';
                        numberCheck.textContent = '✗ One number';
                    }
                });

                // Password confirmation validation
                document.getElementById('password-confirm').addEventListener('input', function() {
                    const password = document.getElementById('password').value;
                    const confirmPassword = this.value;
                    const errorDiv = document.getElementById('password-match-error');

                    if (confirmPassword && password !== confirmPassword) {
                        errorDiv.classList.remove('hidden');
                        this.classList.add('border-red-500');
                    } else {
                        errorDiv.classList.add('hidden');
                        this.classList.remove('border-red-500');
                    }
                });

                // Email validation
                document.getElementById('email').addEventListener('blur', function() {
                    const email = this.value;
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const errorDiv = document.getElementById('email-error');

                    if (email && !emailRegex.test(email)) {
                        errorDiv.classList.remove('hidden');
                        this.classList.add('border-red-500');
                    } else {
                        errorDiv.classList.add('hidden');
                        this.classList.remove('border-red-500');
                    }
                });

                // Name validation
                document.getElementById('name').addEventListener('blur', function() {
                    const name = this.value.trim();
                    const errorDiv = document.getElementById('name-error');

                    if (name.length < 2) {
                        errorDiv.classList.remove('hidden');
                        this.classList.add('border-red-500');
                    } else {
                        errorDiv.classList.add('hidden');
                        this.classList.remove('border-red-500');
                    }
                });

                // Form submission validation
                document.getElementById('registerForm').addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('password-confirm').value;
                    const terms = document.getElementById('terms').checked;
                    const name = document.getElementById('name').value.trim();
                    const email = document.getElementById('email').value;
                    const btn = document.getElementById('registerBtn');

                    let isValid = true;

                    // Validate password strength
                    if (password.length < 8 || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password)) {
                        alert('Please ensure your password meets all requirements.');
                        isValid = false;
                    }

                    // Validate password confirmation
                    if (password !== confirmPassword) {
                        alert('Passwords do not match.');
                        isValid = false;
                    }

                    // Validate terms
                    if (!terms) {
                        document.getElementById('terms-error').classList.remove('hidden');
                        isValid = false;
                    }

                    // Validate name
                    if (name.length < 2) {
                        alert('Name must be at least 2 characters long.');
                        isValid = false;
                    }

                    // Validate email
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        alert('Please enter a valid email address.');
                        isValid = false;
                    }

                    if (!isValid) {
                        e.preventDefault();
                        return false;
                    }

                    // Disable button and show loading state
                    btn.disabled = true;
                    btn.textContent = 'CREATING ACCOUNT...';

                    // Re-enable button after 5 seconds to prevent indefinite disable
                    setTimeout(() => {
                        btn.disabled = false;
                        btn.textContent = 'SIGN UP';
                    }, 5000);
                });

                // Terms checkbox validation
                document.getElementById('terms').addEventListener('change', function() {
                    const errorDiv = document.getElementById('terms-error');
                    if (this.checked) {
                        errorDiv.classList.add('hidden');
                    }
                });
            </script>
        </section>
    </main>
</div>
@endsection

