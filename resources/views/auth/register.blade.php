<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign Up — {{$siteSettings->app_name ?? config('app.name')}}</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
  @vite([
    'resources/css/app.css',
    'resources/js/app.js',
  ])
  <style>body{font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}[x-cloak]{display:none !important}</style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-lg w-full">
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
      <div class="p-6 sm:p-8">
        <div class="mb-6 text-center">
          <div class="inline-flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#06b6d4,#0ea5a4);">
              @if(!empty($siteSettings->brand_logo))
                <img src="{{ asset('storage/' . $siteSettings->brand_logo) }}" alt="Brand Logo" class="w-28 h-28 object-contain" style="max-width:112px;max-height:112px;" />
              @else
                <i class="fa-solid fa-car text-white"></i>
              @endif
            </div>
            <div>
              <h2 class="text-xl font-semibold">Create an account</h2>
              <p class="text-sm text-gray-500">Register to start managing parking slots, tickets, and attendants</p>
            </div>
          </div>
        </div>

        @auth
          <div class="alert alert-info" role="alert">
            You are already signed in.
          </div>
        @endauth

        @guest
        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="space-y-4" x-data="{type:'staff', show:false, isSubmitting:false}" x-on:submit="isSubmitting = true" method="POST" action="{{ route('register') }}" autocomplete="on">
          @csrf
          <div>
            <label class="form-label small mb-1">Full name</label>
            <input type="text" name="name" class="form-control rounded-lg" placeholder="Juan Dela Cruz" value="{{ old('name') }}" required autocomplete="name" autofocus>
          </div>

          <div>
            <label class="form-label small mb-1">Email</label>
            <input type="email" name="email" class="form-control rounded-lg" placeholder="you@example.com" value="{{ old('email') }}" required autocomplete="email">
          </div>

          <div>
            <label class="form-label small mb-1">Password</label>
            <div class="input-group">
              <input :type="show ? 'text' : 'password'" name="password" class="form-control rounded-lg" placeholder="Choose a strong password" required autocomplete="new-password">
              <button type="button" class="btn btn-outline-secondary border-start-0" x-on:click="show = !show">Toggle</button>
            </div>
          </div>

          <div>
            <label class="form-label small mb-1">Confirm</label>
            <input type="password" name="password_confirmation" class="form-control rounded-lg" placeholder="Re-type your password" required autocomplete="new-password">
          </div>

          <div class="d-flex align-items-center gap-3">
            <input type="checkbox" id="terms" name="terms" class="form-check-input" {{ old('terms') ? 'checked' : '' }} required>
            <label for="terms" class="small text-muted mb-0">I agree to the <a href="#" class="text-teal-600">terms & conditions</a></label>
          </div>

          <div>
            <button type="submit" :disabled="isSubmitting" class="w-full btn py-2 rounded-lg d-inline-flex align-items-center justify-content-center" style="background:linear-gradient(90deg,#06b6d4,#0ea5a4);color:#fff;border:none;">
              <span x-cloak x-show="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <span x-text="isSubmitting ? 'Creating…' : 'Create account'"></span>
            </button>
          </div>

          <p class="text-center text-sm text-gray-500">Already registered?
            @if (Route::has('login'))
              <a href="{{ route('login') }}" class="font-medium text-teal-600">Sign in</a>
            @endif
          </p>
        </form>
        @endguest
      </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-4">© <span id="year"></span> {{$siteSettings->app_name ?? config('app.name')}} • Privacy • Terms • Contact</p>
  </div>

  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
