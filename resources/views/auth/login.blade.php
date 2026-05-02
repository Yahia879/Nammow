@php
$customizerHidden = 'customizer-hide';
$configData = Helper::appClasses();
app()->setLocale('ar');
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
    max-width: 550px;
    width: 100%;
    padding: 2rem;
  }
  .dashboard-metrics {
    margin-top: 3rem;
    animation: float-soft 8s ease-in-out infinite;
  }
  @keyframes float-soft {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-15px); }
  }
  .metric-card {
    background: #fff;
    border-radius: 14px;
    padding: 1.25rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.03);
    border: 1px solid rgba(115, 103, 240, 0.08);
    transition: all 0.3s ease;
    text-align: right;
  }
  .metric-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.05);
  }
  .metric-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
  }
  .hero-logo {
    margin-bottom: 1.5rem;
  }
  .hero-title {
    color: #5d596c;
    font-weight: 700;
  }
  .hero-subtitle {
    color: #7367f0;
    font-weight: 600;
  }
  [dir="ltr"] .metric-card {
    text-align: left;
  }
</style>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover authentication-bg">
  <div class="authentication-inner row">
    <!-- Branding Section (SaaS Hero) -->
    <div class="d-none d-lg-flex col-lg-7 p-0">
      <div class="auth-cover-bg w-100 d-flex justify-content-center align-items-center">
        <div class="hero-section text-center">
          <div class="hero-logo">
            <img src="{{ asset('assets/img/logo/logo_128.png') }}" alt="HRMS Logo" width="60">
          </div>
          <h1 class="hero-title display-4 mb-2">{{ __('login.title') }}</h1>
          <h2 class="hero-subtitle h4 mb-3">{{ __('login.hero_subtitle') }}</h2>
          <p class="text-muted h5 fw-normal mb-5">{{ __('login.hero_description') }}</p>

          <!-- Dashboard Preview Cards -->
          <div class="dashboard-metrics">
            <div class="row g-4">
              <div class="col-6">
                <div class="metric-card">
                  <div class="metric-icon bg-light-success"><i class="ti ti-chart-bar ti-sm"></i></div>
                  <h4 class="mb-1">94%</h4>
                  <p class="text-muted small mb-0">{{ __('login.dashboard_attendance_today') }}</p>
                </div>
              </div>
              <div class="col-6">
                <div class="metric-card">
                  <div class="metric-icon bg-light-purple"><i class="ti ti-users ti-sm"></i></div>
                  <h4 class="mb-1">250</h4>
                  <p class="text-muted small mb-0">{{ __('login.dashboard_total_employees') }}</p>
                </div>
              </div>
              <div class="col-6">
                <div class="metric-card">
                  <div class="metric-icon bg-light-info"><i class="ti ti-building ti-sm"></i></div>
                  <h4 class="mb-1">12</h4>
                  <p class="text-muted small mb-0">{{ __('login.dashboard_active_companies') }}</p>
                </div>
              </div>
              <div class="col-6">
                <div class="metric-card">
                  <div class="metric-icon bg-light-warning"><i class="ti ti-calendar-stats ti-sm"></i></div>
                  <h4 class="mb-1">18</h4>
                  <p class="text-muted small mb-0">{{ __('login.dashboard_leave_requests') }}</p>
                </div>
              </div>
            </div>
          </div>
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
