@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
@endphp

@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
{{-- Page Css files --}}
<link rel="stylesheet" href="{{ asset(mix('assets/vendor/css/pages/page-auth.css')) }}">
<style>
  .auth-cover-bg {
    background-color: #f8f7fa !important;
  }
</style>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">
    <!-- Branding Section (Ultra-Minimalist) -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg w-100 d-flex justify-content-center align-items-center">
        <div class="text-center p-5">
          <img src="{{ asset('assets/img/logo/logo_128.png') }}" alt="HRMS Logo" width="80" class="mb-4">
          <h1 class="fw-bold mb-2 text-primary display-4">{{ __('login.title') }}</h1>
          <p class="h4 text-muted fw-normal">{{ __('login.subtitle') }}</p>
        </div>
      </div>
    </div>
    <!-- /Branding Section -->

    <!-- Login -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <h3 class="mb-1">{{ __('Welcome to') . " " . __(env('APP_NAME', 'HRMS')), }}! 👋</h3>
        <p class="mb-4">{{ __('Please sign-in to your account') }}</p>

        @if (session('status'))
        <div class="alert alert-success mb-1 rounded-0" role="alert">
          <div class="alert-body">
            {{ session('status') }}
          </div>
        </div>
        @endif

        <form id="formAuthentication" class="mb-3" action="{{ route('login') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label for="login" class="form-label">{{ __('Email or Employee ID') }}</label>
            <input type="text" class="form-control @error('login') is-invalid @enderror" id="login" name="login" placeholder="example@namaa.sy" autofocus value="{{ old('login') }}">
            @error('login')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3 form-password-toggle">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="login-password">{{ __('Password') }}</label>
              {{-- @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}">
                <small>Forgot Password?</small>
              </a>
              @endif --}}
            </div>
            <div class="input-group input-group-merge @error('password') is-invalid @enderror">
              <input type="password" id="login-password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
              <span class="input-group-text cursor-pointer"><i class="ti ti-eye-off"></i></span>
            </div>
            @error('password')
            <span class="invalid-feedback" role="alert">
              <span class="fw-medium">{{ $message }}</span>
            </span>
            @enderror
          </div>
          <div class="mb-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }} checked>
              <label class="form-check-label" for="remember-me">
                {{ __('Remember Me') }}
              </label>
            </div>
          </div>
          <button class="btn btn-primary d-grid w-100" type="submit">{{ __('Sign in') }}</button>
        </form>
      </div>
    </div>
    <!-- /Login -->
  </div>
</div>
@endsection
