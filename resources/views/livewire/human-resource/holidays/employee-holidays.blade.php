<div>
    @section('title', __('Company Holidays'))

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Upcoming Holidays') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('Search holidays...') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" wire:model.live="date_from" class="form-control" placeholder="{{ __('From Date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" wire:model.live="date_to" class="form-control" placeholder="{{ __('To Date') }}">
                        </div>
                        <div class="col-md-2">
                            <button wire:click="resetFilters" class="btn btn-label-primary w-100">
                                <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                            </button>
                        </div>
                    </div>

                    <div class="row g-4">
                        @forelse($holidays as $holiday)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-none border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">{{ __($holiday->title) }}</h5>
                                            <span class="badge bg-label-success">{{ __('Holiday') }}</span>
                                        </div>
                                        <p class="card-text small text-muted mb-3">
                                            {{ $holiday->description ? __($holiday->description) : __('No additional details provided.') }}
                                        </p>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="ti ti-calendar-event me-2 text-primary"></i>
                                            <span class="small fw-bold">{{ __('Starts') }}:</span>
                                            <span class="small ms-2">{{ $holiday->start_date->translatedFormat('l, F j, Y') }}</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="ti ti-calendar-event me-2 text-danger"></i>
                                            <span class="small fw-bold">{{ __('Ends') }}:</span>
                                            <span class="small ms-2">{{ $holiday->end_date->translatedFormat('l, F j, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-top py-2">
                                        <small class="text-muted">
                                            {{ __('Duration') }}: {{ $holiday->start_date->diffInDays($holiday->end_date) + 1 }} {{ __('days') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <i class="ti ti-christmas-tree ti-lg text-muted mb-3 d-block" style="font-size: 3rem !important;"></i>
                                <h6 class="text-muted">{{ __('No upcoming holidays scheduled.') }}</h6>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4">
                        {{ $holidays->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
