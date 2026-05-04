<div>
    @section('title', __('Salary Management'))

    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">{{ __('Filters') }}</h5>
            <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="{{ __('Search Employee...') }}" wire:model.live.debounce.300ms="searchTerm">
                </div>
                @if(Auth::user()->hasAnyRole(['super_admin', 'client']))
                <div class="col-md-4">
                    <select class="form-select" wire:model.live="filter_company">
                        <option value="">{{ __('All Companies') }}</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr class="text-uppercase small">
                        <th style="width: 30%;">{{ __('Employee') }}</th>
                        <th style="width: 25%;">{{ __('Basic Salary') }}</th>
                        <th style="width: 25%;">{{ __('Total Net') }}</th>
                        <th style="width: 20%;" class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($employees as $employee)
                    <tr wire:key="salary-emp-{{ $employee->id }}">
                        <td class="py-3">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-truncate">{{ $employee->full_name }}</span>
                                <small class="text-muted text-truncate">{{ $employee->company?->name }}</small>
                            </div>
                        </td>
                        <td class="py-3">
                            @if($employee->salary)
                            <span class="fw-bold">{{ number_format($employee->salary->amount, 2) }}</span>
                            @else
                            <span class="text-muted small italic">{{ __('Not Set') }}</span>
                            @endif
                        </td>
                        <td class="py-3">
                            @if($employee->salary)
                            <span class="fw-bold text-success">{{ number_format($employee->salary->net_salary, 2) }}</span>
                            @else
                            <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @if(Auth::user()->canAction('manage_employee_salary'))
                            <button type="button" class="btn btn-sm btn-icon btn-label-primary waves-effect" wire:click="editSalary({{ $employee->id }})" data-bs-toggle="modal" data-bs-target="#salaryModal">
                                <i class="ti ti-pencil ti-xs"></i>
                            </button>
                            @else
                            <i class="ti ti-lock text-muted opacity-25"></i>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted small italic">{{ __('No employees found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-2">
            {{ $employees->links() }}
        </div>
    </div>

    <!-- Salary Modal -->
    <div wire:ignore.self class="modal fade" id="salaryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? __('Update Salary') : __('Set Initial Salary') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveSalary">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('Basic Salary') }}</label>
                            <input type="number" step="0.01" class="form-control" wire:model.live="amount">
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Allowances') }}</label>
                                <input type="number" step="0.01" class="form-control" wire:model.live="allowances">
                                @error('allowances') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Tax') }}</label>
                                <input type="number" step="0.01" class="form-control text-danger" wire:model.live="tax">
                                @error('tax') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Insurance') }}</label>
                                <input type="number" step="0.01" class="form-control text-danger" wire:model.live="insurance">
                                @error('insurance') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">{{ __('Effective Date') }}</label>
                                <input type="date" class="form-control" wire:model="effective_date">
                                @error('effective_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="alert alert-info py-2 mb-0 mt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold">{{ __('Calculated Net:') }}</span>
                                <span class="h5 mb-0 fw-bold">{{ number_format($this->netSalary, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Salary') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
