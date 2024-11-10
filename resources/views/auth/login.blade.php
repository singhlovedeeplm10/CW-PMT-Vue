@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6" >
            <div class="card shadow-lg" style="border: none; border-radius: 20px; width: 90%; margin-top: 15px;">
                <div class="card-header text-center" style="background-color: #f0f3f5; border-bottom: none; padding-top: 2rem;">
                    <!-- Image with Circular Shape -->
                    <img src="img/CWlogo.jpeg" alt="Your Logo" class="img-fluid rounded-circle shadow" style="max-width: 120px;">
                    <!-- Text below the image -->
                    <h4 class="mt-3" style="font-weight: bold; color: #2c3e50;">Welcome to Contriwhiz</h4>
                </div>

                <div class="card-body" style="padding: 2rem;">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <!-- Email Input with Icon -->
                        <div class="mb-4">
                            <label for="email" class="form-label" style="font-weight: bold; color: #2c3e50;">{{ __('Email Address') }}</label>
                            <div class="input-group">
                                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                <span class="input-group-text">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Input with Icon -->
                        <div class="mb-4">
                            <label for="password" class="form-label" style="font-weight: bold; color: #2c3e50;">{{ __('Password') }}</label>
                            <div class="input-group">
                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-0">
                            <button type="submit" class="btn btn-primary btn-lg w-100" style="border-radius: 8px;">
                                {{ __('Login') }}
                            </button>
                        </div>

                        {{-- @if (Route::has('password.request'))
                            <div class="mt-3 text-center">
                                <a href="{{ route('password.request') }}" class="text-decoration-none" style="font-size: 0.9rem;">
                                    {{ __('Forgot Your Password?') }}
                                </a>
                            </div>
                        @endif --}}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Google reCAPTCHA script -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<!-- Font Awesome for Icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Styles for the login page -->
<style>
    /* Card shadow effect */
    .card {
        background-color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-10px);
    }

    /* Circular image styling */
    .rounded-circle {
        border-radius: 50%;
        border: 4px solid #3498db;
        padding: 5px;
        background-color: white;
    }

    /* Title styling */
    h4 {
        font-size: 1.8rem;
        font-weight: bold;
        color: #2980b9;
        margin-top: 1.5rem;
    }

    /* Input field styling */
    .form-control-lg {
        font-size: 1.1rem;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #ced4da;
        transition: box-shadow 0.3s ease, border-color 0.3s ease;
    }

    /* Input field hover and focus effect */
    .form-control-lg:hover {
        border-color: #3498db;
    }

    .form-control-lg:focus {
        box-shadow: 0 0 10px rgba(52, 152, 219, 0.4);
        border-color: #2980b9;
    }

    /* Button styling */
    .btn-primary {
        background-color: #3498db;
        border: none;
        transition: background-color 0.3s ease, transform 0.3s ease;
        padding: 15px;
        font-weight: bold;
    }

    .btn-primary:hover {
        background-color: #2980b9;
        transform: translateY(-3px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    /* Forgot Password link styling */
    .text-decoration-none {
        color: #3498db;
        transition: color 0.3s ease;
    }

    .text-decoration-none:hover {
        color: #2980b9;
    }

    /* Icon Styling */
    .input-group-text {
        background-color: #f8f9fa;
        border-left: 1px solid #ced4da;
        padding: 10px;
    }

    /* Align icons properly inside the input group */
    .fas {
        color: #3498db;
    }
</style>
@endsection
