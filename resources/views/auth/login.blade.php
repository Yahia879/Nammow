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
    position: relative;
    overflow: hidden;
  }
  .hero-section {
    max-width: 500px;
    width: 100%;
    padding: 2rem;
    position: relative;
    z-index: 5;
  }
  .orbital-container {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    pointer-events: none;
  }
  .metric-card {
    position: absolute;
    background: #fff;
    border-radius: 14px;
    padding: 1rem 1.25rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.04);
    border: 1px solid rgba(115, 103, 240, 0.08);
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 170px;
    pointer-events: auto;
    text-align: right;
  }
  
  /* Orbital Positions & Animations */
  .card-attendance { top: 15%; left: 10%; animation: orbit-1 12s ease-in-out infinite; }
  .card-employees  { top: 15%; right: 10%; animation: orbit-2 14s ease-in-out infinite; }
  .card-companies  { bottom: 15%; left: 10%; animation: orbit-3 13s ease-in-out infinite; }
  .card-leaves     { bottom: 15%; right: 10%; animation: orbit-4 15s ease-in-out infinite; }

  @keyframes orbit-1 {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(15px, 10px); }
  }
  @keyframes orbit-2 {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-15px, 15px); }
  }
  @keyframes orbit-3 {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(10px, -15px); }
  }
  @keyframes orbit-4 {
    0%, 100% { transform: translate(0, 0); }
    50% { transform: translate(-10px, -10px); }
  }

  .metric-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .bg-light-purple { background: rgba(115, 103, 240, 0.1); color: #7367f0; }
  .bg-light-success { background: rgba(40, 199, 111, 0.1); color: #28c76f; }
  .bg-light-info { background: rgba(0, 207, 221, 0.1); color: #00cfdd; }
  .bg-light-warning { background: rgba(255, 159, 67, 0.1); color: #ff9f43; }

  .hero-logo { margin-bottom: 1rem; }
  .hero-title { color: #5d596c; font-weight: 700; margin-bottom: 0.5rem; }
  .hero-subtitle { color: #7367f0; font-weight: 600; margin-bottom: 1rem; }
  
  [dir="ltr"] .metric-card { text-align: left; }
  
  /* Responsive */
  @media (max-width: 1400px) {
    .metric-card { min-width: 150px; padding: 0.75rem 1rem; }
    .card-attendance, .card-companies { left: 5%; }
    .card-employees, .card-leaves { right: 5%; }
  }

  /* Dark Mode Enhancements */
  .dark-style .auth-cover-bg {
    background-color: #25293c !important;
  }

  .dark-style .metric-card {
    background: #2f3349 !important;
    border-color: rgba(225, 222, 245, 0.12) !important;
    box-shadow: 0 10px 30px rgba(15, 20, 34, 0.4) !important;
  }

  .dark-style .metric-card h5 {
    color: #cfd3ed !important;
  }

  .dark-style .metric-card .text-muted {
    color: #a3a7cc !important;
  }

  .dark-style .hero-title {
    color: #daddf5 !important;
  }

  .dark-style .hero-section .text-muted {
    color: #a3a7cc !important;
  }

  .dark-style .authentication-inner {
    background-color: #2f3349 !important;
  }

  .dark-style .col-lg-5.align-items-center {
    background-color: #2f3349 !important;
  }

  .dark-style h3 {
    color: #cfd3ed !important;
  }

  .dark-style .w-px-400 p.mb-4 {
    color: #a3a7cc !important;
  }

  .dark-style .form-label {
    color: #cfd3ed !important;
  }

  .dark-style .form-control {
    background-color: #2f3349 !important;
    border-color: #434968 !important;
    color: #cfd3ed !important;
  }

  .dark-style .form-control:focus {
    border-color: #7367f0 !important;
  }

  .dark-style .input-group-text {
    background-color: #2f3349 !important;
    border-color: #434968 !important;
    color: #7983bb !important;
  }

  .dark-style .form-control::placeholder {
    color: #7983bb !important;
  }

  .dark-style .form-check-label {
    color: #a3a7cc !important;
  }
</style>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">
    <!-- Branding Section (SaaS Hero) -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg w-100 d-flex justify-content-center align-items-center position-relative">
        
        <!-- Orbital Metrics -->
        <div class="orbital-container">
          <div class="metric-card card-attendance">
            <div class="metric-icon bg-light-success"><i class="ti ti-chart-bar ti-xs"></i></div>
            <div>
              <h5 class="mb-0">94%</h5>
              <p class="text-muted extra-small mb-0">{{ __('login.dashboard_attendance_today') }}</p>
            </div>
          </div>
          <div class="metric-card card-employees">
            <div class="metric-icon bg-light-purple"><i class="ti ti-users ti-xs"></i></div>
            <div>
              <h5 class="mb-0">250</h5>
              <p class="text-muted extra-small mb-0">{{ __('login.dashboard_total_employees') }}</p>
            </div>
          </div>
          <div class="metric-card card-companies">
            <div class="metric-icon bg-light-info"><i class="ti ti-building ti-xs"></i></div>
            <div>
              <h5 class="mb-0">12</h5>
              <p class="text-muted extra-small mb-0">{{ __('login.dashboard_active_companies') }}</p>
            </div>
          </div>
          <div class="metric-card card-leaves">
            <div class="metric-icon bg-light-warning"><i class="ti ti-calendar-stats ti-xs"></i></div>
            <div>
              <h5 class="mb-0">18</h5>
              <p class="text-muted extra-small mb-0">{{ __('login.dashboard_leave_requests') }}</p>
            </div>
          </div>
        </div>

        <div class="hero-section text-center">
          <div class="hero-logo">
            <img src="{{ asset('assets/img/logo/icon.png') }}" alt="HRMS Logo" width="70">
          </div>
          <h1 class="hero-title display-5">{{ __('login.title') }}</h1>
          <h2 class="hero-subtitle h5">{{ __('login.hero_subtitle') }}</h2>
          <p class="text-muted h6 fw-normal">{{ __('login.hero_description') }}</p>
        </div>
      </div>
    </div>
    <!-- /Branding Section -->

    <!-- Login -->
    <div class="d-flex col-12 col-lg-5 align-items-center p-sm-5 p-4">
      <div class="w-px-400 mx-auto">
        <h3 class="mb-1">{{ __('login.form_title') }}! 👋</h3>
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
            <input type="text" class="form-control @error('login') is-invalid @enderror" id="login" name="login" placeholder="example@nammow.sa" autofocus value="{{ old('login') }}">
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
