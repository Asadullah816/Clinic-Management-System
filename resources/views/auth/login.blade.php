<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar skin care & Aesthethic clinic')) }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Google Fonts: Plus Jakarta Sans --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --cms-font: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --cms-primary: #0284c7;
            --cms-primary-dark: #0369a1;
        }

        body {
            font-family: var(--cms-font);
            min-height: 100vh;
            margin: 0;
            background: 
                radial-gradient(circle at 15% 15%, rgba(2, 132, 199, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(14, 165, 233, 0.18) 0%, transparent 45%),
                linear-gradient(180deg, #0b132b 0%, #0f172a 60%, #172545 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            animation: cardAppear 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardAppear {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .brand-icon-box {
            width: 58px;
            height: 58px;
            border-radius: 1rem;
            background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);
            box-shadow: 0 10px 25px -4px rgba(2, 132, 199, 0.5);
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .form-label {
            font-size: 0.84rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 0.4rem;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #64748b;
            border-top-left-radius: 0.625rem;
            border-bottom-left-radius: 0.625rem;
            font-size: 0.95rem;
            padding-left: 0.9rem;
            padding-right: 0.9rem;
        }

        .form-control {
            border-color: #cbd5e1;
            font-size: 0.885rem;
            padding: 0.65rem 0.85rem;
            color: #0f172a;
            background-color: #ffffff;
            transition: all 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: var(--cms-primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
            background-color: #ffffff;
        }

        .btn-toggle-password {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-left: none;
            color: #64748b;
            border-top-right-radius: 0.625rem;
            border-bottom-right-radius: 0.625rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .btn-toggle-password:hover {
            color: #0f172a;
            background-color: #f8fafc;
        }

        .btn-submit {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: none;
            border-radius: 0.625rem;
            padding: 0.75rem 1rem;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.925rem;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
            transition: all 0.2s ease;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            box-shadow: 0 6px 18px rgba(2, 132, 199, 0.45);
            transform: translateY(-1px);
            color: #ffffff;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .footer-text {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.785rem;
            letter-spacing: 0.01em;
            text-align: center;
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="p-4 p-sm-4 pt-sm-4 pb-sm-4">

            {{-- Brand Header --}}
            <div class="text-center mb-4 pt-2">
                <div class="brand-icon-box mb-3">
                    <i class="bi bi-heart-pulse text-white fs-3"></i>
                </div>
                <h4 class="fw-bold mb-1" style="color: #0f172a; letter-spacing: -0.02em;">
                    {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar skin care & Aesthethic clinic')) }}
                </h4>
                <p class="text-muted small mb-0">Clinic Management &amp; Aesthetic Care</p>
            </div>

            {{-- Error Alerts --}}
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 mb-4 rounded-3 d-flex align-items-center" style="font-size: 0.85rem;">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf

                {{-- Email Input --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="form-control rounded-end @error('email') is-invalid @enderror"
                            placeholder="doctor@clinic.com" required autofocus>
                    </div>
                </div>

                {{-- Password Input with toggle --}}
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Enter your password" required>
                        <button type="button" class="btn btn-toggle-password" id="togglePassword" tabindex="-1" title="Show/Hide Password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-submit d-flex align-items-center justify-content-center">
                        <span>Sign In</span>
                        <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>

                {{-- Security Badge --}}
                <div class="text-center pt-2">
                    <span class="text-muted small" style="font-size: 0.75rem;">
                        <i class="bi bi-lock-fill text-secondary me-1"></i> Authorized Personnel Only &middot; Secure System
                    </span>
                </div>
            </form>

        </div>
    </div>

    {{-- Footer Info --}}
    <div class="footer-text">
        &copy; {{ date('Y') }} {{ \App\Models\Setting::get('clinic_name', config('app.name', 'Mayar skin care & Aesthethic clinic')) }}
        <br>
        <span class="opacity-75">Aesthetic &amp; Skin Care Clinic Management System</span>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Toggle Password Visibility Script --}}
    <script>
        document.getElementById('togglePassword')?.addEventListener('click', function () {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>

</html>
