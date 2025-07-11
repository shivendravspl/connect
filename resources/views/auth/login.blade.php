<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connect - Sign In</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --connect-primary: #4f46e5;
            --connect-secondary: #7c3aed;
            --connect-accent: #06b6d4;
            --connect-dark: #1e293b;
            --connect-light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        html {
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .login-container {
            height: 100vh;
            display: flex;
            background: linear-gradient(135deg, var(--connect-primary) 0%, var(--connect-secondary) 50%, var(--connect-accent) 100%);
            position: relative;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        .left-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 2;
        }

        .brand-section {
            text-align: center;
            color: white;
            max-width: 400px;
            padding: 2rem;
        }

        .connect-logo {
            font-size: 4rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, #ffffff, #e0e7ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
            letter-spacing: -2px;
        }

        .brand-tagline {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .feature-list {
            list-style: none;
            text-align: left;
        }

        .feature-list li {
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            opacity: 0.8;
        }

        .feature-list li::before {
            content: '✓';
            margin-right: 1rem;
            color: #10b981;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .right-panel {
            flex: 1;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: -10px 0 50px rgba(0, 0, 0, 0.1);
        }

        .login-form-container {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            position: relative;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            color: var(--connect-dark);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: #64748b;
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--connect-dark);
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--connect-primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.2s;
        }

        .password-toggle:hover {
            color: var(--connect-primary);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 1.1rem;
            height: 1.1rem;
            accent-color: var(--connect-primary);
        }

        .forgot-password {
            color: var(--connect-primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .forgot-password:hover {
            opacity: 0.8;
            text-decoration: underline;
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--connect-primary), var(--connect-secondary));
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 0.75rem;
            border: none;
            margin-bottom: 1.5rem;
            padding: 1rem;
        }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .error-message {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }

        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 15s infinite ease-in-out;
        }

        .shape-1 {
            top: 20%;
            left: 10%;
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            animation-delay: -5s;
        }

        .shape-2 {
            top: 60%;
            left: 80%;
            width: 80px;
            height: 80px;
            background: white;
            transform: rotate(45deg);
            animation-delay: -10s;
        }

        .shape-3 {
            top: 80%;
            left: 20%;
            width: 60px;
            height: 60px;
            background: white;
            clip-path: polygon(50% 0%, 0% 100%, 100% 100%);
            animation-delay: -2s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            33% {
                transform: translateY(-20px) rotate(120deg);
            }

            66% {
                transform: translateY(10px) rotate(240deg);
            }
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .left-panel {
                flex: 0.4;
                padding: 1rem;
            }

            .connect-logo {
                font-size: 2.5rem;
            }

            .brand-tagline {
                font-size: 1rem;
            }

            .feature-list {
                display: none;
            }

            .right-panel {
                flex: 0.6;
                box-shadow: none;
            }

            .login-form-container {
                padding: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
        </div>

        <div class="left-panel">
            <div class="brand-section">
                <div class="connect-logo">Connect</div>             
            </div>
        </div>

        <div class="right-panel">
            <div class="login-form-container">
                <div class="form-header">
                    <h2>Welcome Back!</h2>
                    <p>Sign in to continue to Connect</p>
                </div>

                @if (session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="login">Email or Phone</label>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            placeholder="Enter email or phone number"
                            class="form-control @error('login') is-invalid @enderror"
                            required
                            value="{{ old('login') }}"
                            autofocus
                            tabindex="1">
                        @error('login')
                        <div class="error-message">
                            <i class="ri-error-warning-line"></i>
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-container">
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Enter password"
                                id="password-input"
                                name="password"
                                tabindex="2">
                            <button
                                class="password-toggle"
                                type="button"
                                id="password-addon"
                                tabindex="3">
                                <i class="ri-eye-fill"></i>
                            </button>
                        </div>
                        @error('password')
                        <div class="error-message">
                            <i class="ri-error-warning-line"></i>
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="form-options">
                        <div class="remember-me">
                            <input
                                type="checkbox"
                                id="remember"
                                name="remember"
                                tabindex="4"
                                {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Remember me</label>
                        </div>

                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-password">
                            Forgot Password?
                        </a>
                        @endif
                    </div>

                    <button class="login-btn" type="submit" tabindex="5">
                        Sign In
                    </button>
                    <div class="register-link">
                        <p>Don't have an account?
                            <a href="{{ route('register') }}">Register</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{--@include('layouts.footer') --}}

    <script>
        // Password visibility toggle
        document.getElementById('password-addon').addEventListener('click', function() {
            const passwordInput = document.getElementById('password-input');
            const icon = this.querySelector('i');

            if (passwordInput.getAttribute('type') === 'password') {
                passwordInput.setAttribute('type', 'text');
                icon.classList.remove('ri-eye-fill');
                icon.classList.add('ri-eye-off-fill');
            } else {
                passwordInput.setAttribute('type', 'password');
                icon.classList.remove('ri-eye-off-fill');
                icon.classList.add('ri-eye-fill');
            }
        });

        // Add subtle animations on load
        document.addEventListener('DOMContentLoaded', function() {
            const formContainer = document.querySelector('.login-form-container');
            const brandSection = document.querySelector('.brand-section');

            formContainer.style.opacity = '0';
            formContainer.style.transform = 'translateX(30px)';
            brandSection.style.opacity = '0';
            brandSection.style.transform = 'translateX(-30px)';

            setTimeout(() => {
                formContainer.style.transition = 'all 0.8s ease';
                brandSection.style.transition = 'all 0.8s ease';
                formContainer.style.opacity = '1';
                formContainer.style.transform = 'translateX(0)';
                brandSection.style.opacity = '1';
                brandSection.style.transform = 'translateX(0)';
            }, 100);
        });
    </script>
</body>

</html>