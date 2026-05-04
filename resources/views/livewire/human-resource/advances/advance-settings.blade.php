<div>
    @section('title', __('Advance Settings'))

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-end">
                        @if(Auth::user()->hasAnyRole(['super_admin', 'client']))
                        <div class="col-md-8">
                            <label class="form-label fw-bold">{{ __('Select Company to Configure') }}</label>
                            <select class="form-select" wire:model.live="selected_company_id">
                                @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        <div class="col-md-8">
                            <h5 class="mb-0">{{ __('Configuring Settings for:') }} <span class="badge bg-label-primary">{{ Auth::user()->company->name ?? '' }}</span></h5>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Advance & Loan Rules') }}</h5>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_enabled" wire:model="is_enabled">
                                <label class="form-check-label" for="is_enabled">{{ __('Enable Advances Module') }}</label>
                            </div>
                            <small class="text-muted">{{ __('Toggle to allow or prevent employees from requesting advances.') }}</small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Max Advance Type') }}</label>
                                <select class="form-select" wire:model="max_advance_type">
                                    <option value="fixed">{{ __('Fixed Amount') }}</option>
                                    <option value="percentage">{{ __('Percentage of Salary') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Max Value') }}</label>
                                <input type="number" step="0.01" class="form-control" wire:model="max_advance_value">
                                @error('max_advance_value') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Maximum Installments') }}</label>
                            <input type="number" class="form-control" wire:model="max_installments">
                            @error('max_installments') <span class="text-danger small">{{ $message }}</span> @enderror
                            <small class="text-muted">{{ __('The maximum number of months an employee can spread the repayment over.') }}</small>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="allow_new" wire:model="allow_new_advance_with_open_balance">
                                <label class="form-check-label" for="allow_new">{{ __('Allow New Advance with Open Balance') }}</label>
                            </div>
                            <small class="text-muted">{{ __('If enabled, employees can request a new advance even if they have an unpaid balance from a previous one.') }}</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="save">{{ __('Save Settings') }}</span>
                                <span wire:loading wire:target="save" class="spinner-border spinner-border-sm" role="status"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
