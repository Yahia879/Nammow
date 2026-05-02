<div>
    @section('title', __('Advance Management'))

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ __('Filters') }}</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select class="form-select" wire:model.live="filter_status">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending">{{ __('Pending') }}</option>
                        <option value="approved">{{ __('Approved') }}</option>
                        <option value="rejected">{{ __('Rejected') }}</option>
                        <option value="cancelled">{{ __('Cancelled') }}</option>
                    </select>
                </div>
                @if(Auth::user()->hasAnyRole(['super_admin', 'client']))
                <div class="col-md-3">
                    <label class="form-label">{{ __('Company') }}</label>
                    <select class="form-select" wire:model.live="filter_company">
                        <option value="">{{ __('All Companies') }}</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label">{{ __('Employee') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Search Employee...') }}" wire:model.live.debounce.300ms="filter_employee">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('From Date') }}</label>
                    <input type="date" class="form-control" wire:model.live="filter_date_from">
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('To Date') }}</label>
                    <input type="date" class="form-control" wire:model.live="filter_date_to">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-2">
            <h5 class="mb-0">{{ __('Advances & Loans Requests') }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="table-layout: fixed; width: 100%;">
                <thead>
                    <tr class="text-uppercase small">
                        <th style="width: 25%;">{{ __('Employee') }}</th>
                        <th style="width: 20%;">{{ __('Amounts') }}</th>
                        <th style="width: 15%;" class="text-center">{{ __('Installments') }}</th>
                        <th style="width: 20%;" class="text-center">{{ __('Status') }}</th>
                        <th style="width: 20%;" class="text-center">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($advances as $advance)
                    <tr>
                        <td class="py-3">
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-truncate">{{ $advance->employee?->full_name }}</span>
                                <small class="text-muted text-truncate" style="font-size: 0.75rem;">
                                    {{ $advance->created_at->translatedFormat('d M, Y') }} @if(Auth::user()->hasAnyRole(['super_admin', 'client'])) • {{ $advance->company?->name }} @endif
                                </small>
                            </div>
                        </td>
                        <td class="py-3">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-label-secondary fw-bold" title="{{ __('Requested') }}">{{ number_format($advance->requested_amount, 0) }}</span>
                                @if($advance->approved_amount)
                                <i class="ti ti-arrow-narrow-right text-muted"></i>
                                <span class="badge bg-label-success fw-bold" title="{{ __('Approved') }}">{{ number_format($advance->approved_amount, 0) }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3 text-center">
                            @if($advance->number_of_installments)
                            <span class="fw-bold text-info">{{ $advance->number_of_installments }}</span>
                            @else
                            <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td class="py-3 text-center">
                            @php
                                $statusClass = [
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    'cancelled' => 'secondary'
                                ][$advance->status] ?? 'primary';
                            @endphp
                            <span class="badge bg-{{ $statusClass }} px-2 py-1" style="font-size: 0.7rem; border-radius: 4px;">
                                {{ __(strtoupper($advance->status)) }}
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            @if($advance->status === 'pending' && (Auth::user()->canAction('approve_advance') || Auth::user()->canAction('reject_advance')))
                            <div class="d-flex justify-content-center gap-2">
                                @if(Auth::user()->canAction('approve_advance'))
                                <button type="button" class="btn btn-sm btn-icon btn-label-success waves-effect" wire:click="selectAdvance({{ $advance->id }})" data-bs-toggle="modal" data-bs-target="#approveModal" title="{{ __('Approve') }}">
                                    <i class="ti ti-check ti-xs"></i>
                                </button>
                                @endif
                                @if(Auth::user()->canAction('reject_advance'))
                                <button type="button" class="btn btn-sm btn-icon btn-label-danger waves-effect" wire:click="selectAdvance({{ $advance->id }})" data-bs-toggle="modal" data-bs-target="#rejectModal" title="{{ __('Reject') }}">
                                    <i class="ti ti-x ti-xs"></i>
                                </button>
                                @endif
                            </div>
                            @else
                            <span class="text-muted small italic opacity-50">{{ __('Locked') }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="ti ti-info-circle d-block mb-2 ti-lg"></i>
                            {{ __('No requests found.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer py-2">
            {{ $advances->links() }}
        </div>
    </div>

    <!-- Approve Modal -->
    <div wire:ignore.self class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Approve Advance') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="approve">
                    <div class="modal-body">
                        @if($selectedAdvance)
                        <div class="alert alert-primary mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>{{ __('Employee:') }} <strong>{{ $selectedAdvance->employee->full_name }}</strong></span>
                                <span>{{ __('Requested:') }} <strong>{{ number_format($selectedAdvance->requested_amount, 2) }}</strong></span>
                            </div>
                        </div>
                        @endif
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Approved Amount') }}</label>
                            <input type="number" step="0.01" class="form-control" wire:model="approved_amount">
                            @error('approved_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">{{ __('Number of Installments') }}</label>
                            <input type="number" class="form-control" wire:model="number_of_installments">
                            @error('number_of_installments') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-success">{{ __('Confirm Approval') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div wire:ignore.self class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Reject Advance Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="reject">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Rejection Reason') }}</label>
                            <textarea class="form-control" rows="3" wire:model="rejection_reason" placeholder="{{ __('Provide a reason for rejection...') }}"></textarea>
                            @error('rejection_reason') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-danger">{{ __('Confirm Rejection') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
