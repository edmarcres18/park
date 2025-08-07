@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="flex flex-col md:flex-row bg-white rounded-lg shadow-lg overflow-hidden max-w-4xl w-full">
        <!-- Left Panel -->
        <div class="left-panel text-white w-full md:w-1/2 p-12 flex flex-col justify-center items-center text-center">
            <h1 class="text-3xl font-bold mb-4">Already Signed up?</h1>
            <p class="mb-8">Log in to your account so you can continue building and editing your onboarding flows.</p>
            <a href="{{ route('login') }}" class="border-2 border-white rounded-full py-2 px-12 font-semibold hover:bg-white hover:text-blue-700 transition-colors duration-300">LOG IN</a>
        </div>

        <!-- Right Panel -->
        <div class="right-panel w-full md:w-1/2 p-12">
            <h2 class="text-3xl font-bold mb-2">Sign Up for an Account</h2>
            <p class="text-gray-600 mb-8">Let's get you all set up so you can start creating your first onboarding experience.</p>


            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf
                <div class="mb-4">
                    <div>
                        <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name</label>
                        <input id="name" type="text" class="w-full px-4 py-3 rounded-lg bg-gray-200 border focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Your name" maxlength="255" minlength="2">
                        @error('name')
                            <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                        @enderror
                        <div id="name-error" class="text-red-500 text-xs italic mt-2 hidden">Name must be at least 2 characters long.</div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input id="email" type="email" class="w-full px-4 py-3 rounded-lg bg-gray-200 border focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email address" maxlength="255">
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                    <div id="email-error" class="text-red-500 text-xs italic mt-2 hidden">Please enter a valid email address.</div>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input id="password" type="password" class="w-full px-4 py-3 rounded-lg bg-gray-200 border focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror" name="password" required autocomplete="new-password" placeholder="Enter a strong password" minlength="8">
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                    <div id="password-strength" class="text-sm mt-2">
                        <div class="text-gray-600">Password must contain:</div>
                        <div id="length-check" class="text-red-500">✗ At least 8 characters</div>
                        <div id="uppercase-check" class="text-red-500">✗ One uppercase letter</div>
                        <div id="lowercase-check" class="text-red-500">✗ One lowercase letter</div>
                        <div id="number-check" class="text-red-500">✗ One number</div>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="password-confirm" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                    <input id="password-confirm" type="password" class="w-full px-4 py-3 rounded-lg bg-gray-200 border focus:outline-none focus:ring-2 focus:ring-blue-500" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm your password" minlength="8">
                    <div id="password-match-error" class="text-red-500 text-xs italic mt-2 hidden">Passwords do not match.</div>
                </div>

                <div class="mb-6">
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" name="terms" id="terms" required>
                        <span class="ml-2 text-gray-700">I accept the <a href="#" class="text-blue-600 hover:underline" onclick="alert('Terms and Conditions would be displayed here'); return false;">Terms & Conditions</a></span>
                    </label>
                    <div id="terms-error" class="text-red-500 text-xs italic mt-2 hidden">You must accept the terms and conditions.</div>
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-3 px-4 rounded-full transition-colors duration-300 disabled:opacity-50" id="registerBtn">
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
        </div>
    </div>
</div>
@endsection

