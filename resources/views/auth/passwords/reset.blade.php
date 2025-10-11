<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'OJAS - Empowering Financial Decisions') }} - Reset Password</title>
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/custom.min.css') }}" rel="stylesheet">
</head>

<body>
    <div class="auth-page-wrapper auth-bg-cover py-4 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card overflow-hidden card-bg-fill galaxy-border-none">
                            <div class="row g-0">
                                <div class="col-lg-6">
                                    <div class="p-lg-5 p-4 auth-one-bg h-100">
                                        <div class="bg-overlay"></div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div style="margin-left:20%;margin-top:3%;">
                                        <img style="width:190px;" src="{{ asset('assets/images/connect-logo.png') }}" class="logo_image flipImg" draggable="false" alt="Connect Logo">
                                        
                                    </div>
                                    <div class="p-lg-5 p-4">
                                        <div>
                                            <h5 class="text-primary">Reset Password</h5>
                                            <p class="text-muted">Enter your new password below.</p>
                                        </div>
                                        <div class="mt-4">
                                            @if (session('status'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                {{ session('status') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                            @endif
                                            <form method="POST" action="{{ route('password.update') }}">
                                                @csrf
                                                <input type="hidden" name="token" value="{{ $token }}">
                                                <input type="hidden" name="email" value="{{ request('email') }}">

                                                <div class="mb-3">
                                                    <label for="identifier" class="form-label">Email or Phone</label>
                                                    <input type="text" id="identifier" name="identifier"
                                                        value="{{ old('identifier', request('email')) }}"
                                                        class="form-control @error('identifier') is-invalid @enderror" required>
                                                    @error('identifier')
                                                    <p class="text-danger">
                                                        <strong>{{ $message }}</strong>
                                                    </p>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label for="password" class="form-label">New Password</label>
                                                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                                    @error('password')
                                                    <p class="text-danger">
                                                        <strong>{{ $message }}</strong>
                                                    </p>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                                                </div>
                                                <div class="mt-4">
                                                    <button class="btn btn-success w-100" type="submit">Reset Password</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.footer')
</body>

</html>