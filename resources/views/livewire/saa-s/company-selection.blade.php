@php
    $configData = Helper::appClasses();
@endphp

@section('title', __('Select Company'))

@section('page-style')
<style>
    .company-card {
        transition: all 0.3s ease-in-out;
        border: 2px solid transparent !important;
    }
    .company-card:hover {
        transform: translateY(-5px);
        border-color: var(--bs-primary) !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .company-card.active {
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.05);
    }
</style>
@endsection

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row justify-content-center">
        <div class="col-12 text-center mb-5">
            <h2 class="mb-2">
                {{ __('Select the company you want to manage') }}
            </h2>
            <p class="text-muted text-uppercase fw-semibold mb-0">{{ __('Please choose a company to continue') }}</p>
        </div>
    </div>

    @if(count($companies) === 0)
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="alert alert-warning text-center" role="alert">
                    <h4 class="alert-heading mb-2">{{ __('No Companies Found!') }}</h4>
                    <p>{{ __('No companies are currently assigned to your manager account.') }}</p>
                    <hr>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning">
                            <i class="ti ti-logout me-1"></i> {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        <div class="row justify-content-center">
            @foreach($companies as $company)
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 company-card cursor-pointer {{ session('active_company_id') == $company->id ? 'active' : '' }}" 
                         wire:click="selectCompany({{ $company->id }})">
                        <div class="card-body text-center d-flex flex-column">
                            <div class="avatar avatar-xl mx-auto mb-4">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="ti ti-building ti-xl"></i>
                                </span>
                            </div>
                            <h4 class="mb-1">{{ $company->name }}</h4>
                            <p class="text-muted small mb-4">
                                <i class="ti ti-user-star me-1 ti-xs"></i>
                                {{ __('Owner:') }} {{ $company->owner_name }}
                            </p>
                            <div class="mt-auto d-grid">
                                <button class="btn btn-primary btn-wave">
                                    <i class="ti ti-check me-1"></i> {{ __('Select') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

