
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Sign In — Parking System</title>
  <!-- Google Font: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <!-- Bootstrap 5 (CSS) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="" crossorigin="anonymous" />
  <!-- App styles via Vite (Tailwind compiled) -->
  @vite([
    'resources/css/app.css',
    'resources/js/app.js',
  ])
  <style>
    :root{--brand:#0ea5a4;--muted:#6b7280}
    body{font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial}
    /* small helper to make bootstrap form-control match tailwind look */
    .form-control:focus{box-shadow:none;border-color:rgba(14,165,164,.9)}
    [x-cloak]{display:none !important}
  </style>
</head>
<body class="bg-gradient-to-b from-white via-slate-50 to-slate-100 min-h-screen flex items-center justify-center p-4">
  <div class="max-w-md w-full">
    <div class="bg-white/80 backdrop-blur-sm shadow-lg rounded-2xl overflow-hidden">
      <div class="p-6 sm:p-8">
        <div class="flex items-center gap-3 mb-6">
          <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center" style="background:linear-gradient(135deg,#06b6d4, #0ea5a4);">
            @if(!empty($siteSettings->brand_logo))
              <img src="{{ asset('storage/' . $siteSettings->brand_logo) }}" alt="Brand Logo" class="w-28 h-28 object-contain" style="max-width:112px;max-height:112px;" />
            @else
              <i class="fa-solid fa-fw fa-car text-white text-lg"></i>
            @endif
          </div>
          <div>
            <h1 class="text-lg font-semibold">{{$siteSettings->app_name ?? config('app.name')}}</h1>
            <p class="text-sm text-gray-500">Sign in to manage parking sessions & tickets</p>
          </div>
        </div>

        @auth
          <div class="alert alert-info" role="alert">
            You are already signed in. Go to your dashboard.
            @if(auth()->user()->hasRole('admin'))
              <a class="ms-1" href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
            @elseif(auth()->user()->hasRole('attendant'))
              <a class="ms-1" href="{{ route('attendant.dashboard') }}">Attendant Dashboard</a>
            @endif
          </div>
        @endauth

        @guest
        @if (session('success'))
          <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
          <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form class="space-y-4" x-data="{show:false, isSubmitting:false}" x-on:submit="isSubmitting = true" method="POST" action="{{ route('login') }}" autocomplete="on">
          @csrf
          <div>
            <label class="form-label small mb-1">Email</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" name="email" class="form-control flex-1 rounded-start border ps-3" placeholder="you@example.com" value="{{ old('email') }}" required autocomplete="username" autofocus>
            </div>
          </div>

          <div>
            <label class="form-label small mb-1">Password</label>
            <div class="input-group">
              <span class="input-group-text bg-transparent border-end-0"><i class="fa-solid fa-lock"></i></span>
              <input :type="show ? 'text' : 'password'" name="password" class="form-control flex-1 rounded-start border ps-3" placeholder="Enter your password" required autocomplete="current-password">
              <button type="button" class="btn btn-outline-secondary border-start-0" x-on:click="show = !show">
                <span x-text="show ? 'Hide' : 'Show'"></span>
              </button>
            </div>
          </div>

          <div class="d-flex justify-content-between align-items-center">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
              <label class="form-check-label small text-muted" for="remember">Remember me</label>
            </div>
            @if (Route::has('password.request'))
              <a class="small text-decoration-none" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
          </div>

          <div>
            <button type="submit" :disabled="isSubmitting" class="w-full btn btn-primary py-2 rounded-lg d-inline-flex align-items-center justify-content-center" style="background:linear-gradient(90deg,#06b6d4,#0ea5a4);border:none;">
              <span x-cloak x-show="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
              <span x-text="isSubmitting ? 'Signing in…' : 'Sign In'"></span>
            </button>
          </div>
<!--
          <div class="text-center text-muted small">or continue with</div>

          <div class="grid grid-cols-2 gap-3">
            <button type="button" class="btn border rounded-lg py-2 flex items-center justify-center gap-2">
              <i class="fa-brands fa-google"></i><span class="hidden sm:inline">Google</span>
            </button>
            <button type="button" class="btn border rounded-lg py-2 flex items-center justify-center gap-2">
              <i class="fa-brands fa-facebook-f"></i><span class="hidden sm:inline">Facebook</span>
            </button>
          </div> -->

          <p class="text-center text-sm text-gray-500 mt-3">Don't have an account?
            @if (Route::has('register'))
              <a href="{{ route('register') }}" class="font-medium text-teal-600">Create one</a>
            @endif
          </p>
        </form>
        @endguest
      </div>
    </div>

    <p class="text-center text-xs text-gray-400 mt-4">© <span id="year"></span> Parking System • Built for efficiency</p>
  </div>

  <!-- Alpine.js for small interactivity -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
  <!-- Bootstrap Bundle (with Popper) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>
  <script>
    document.getElementById('year').textContent = new Date().getFullYear();
  </script>
</body>
</html>
