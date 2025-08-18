@include('layouts.header')
<style>
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden; /* No scroll */
        font-family: 'Segoe UI', sans-serif;
    }

    body {
        /* Modern login-style gradient */
        background: linear-gradient(135deg, #6fb1fc, #a777e3);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .register-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 12px;
        box-shadow: 0px 8px 25px rgba(0, 0, 0, 0.15);
        padding: 35px 30px;
        width: 100%;
        max-width: 420px;
        animation: fadeIn 0.6s ease-in-out;
        backdrop-filter: blur(5px);
    }

    .register-card h3 {
        font-weight: bold;
        color: #333;
        margin-bottom: 25px;
        text-align: center;
    }

    .form-label {
        font-weight: 500;
    }

    .btn-primary {
        background: #6fb1fc;
        border: none;
        transition: 0.3s;
    }

    .btn-primary:hover {
        background: #539ae4;
        transform: scale(1.02);
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="register-card">
    <h3>Create Your Account</h3>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Full Name*</label>
            <input id="name" type="text"
                   class="form-control @error('name') is-invalid @enderror"
                   name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email*</label>
            <input id="email" type="email"
                   class="form-control @error('email') is-invalid @enderror"
                   name="email" value="{{ old('email') }}" required>
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone*</label>
            <input id="phone" type="text"
                   class="form-control @error('phone') is-invalid @enderror"
                   name="phone" value="{{ old('phone') }}" required>
            @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password*</label>
            <input id="password" type="password"
                   class="form-control @error('password') is-invalid @enderror"
                   name="password" required>
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password*</label>
            <input id="password_confirmation" type="password" class="form-control" name="password_confirmation" required>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
         <p class="text-center mt-3">
            Already have an account?
            <a href="{{ route('login') }}" style="color: #6fb1fc; font-weight: 500;">Login here</a>
        </p>
    </form>
</div>
