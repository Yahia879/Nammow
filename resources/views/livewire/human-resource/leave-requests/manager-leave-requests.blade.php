<div>
    @section('title', __('Manage Leave Requests'))

    @if($selectedEmployee)
    <div class="row">
        <div class="col-12">
            <div class="card mb-4 border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-4">{{ __('Annual Leave Summary') }}: <span class="text-primary">{{ $selectedEmployee->full_name }}</span> ({{ now()->year }})</h5>
                            <div class="row">
                                <div class="col-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-calendar-event"></i></span>
                                        </div>
                                        <h6 class="mb-0 small text-muted text-uppercase">{{ __('Earned') }}</h6>
                                    </div>
                                    <h4 class="mb-0">{{ (int) round($selectedEmployee->earned_annual_leave_days) }} <small class="text-muted">{{ __('Days') }}</small></h4>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-plane-departure"></i></span>
                                        </div>
                                        <h6 class="mb-0 small text-muted text-uppercase">{{ __('Taken') }}</h6>
                                    </div>
                                    <h4 class="mb-0">{{ (int) round($selectedEmployee->taken_annual_leave_days) }} <small class="text-muted">{{ __('Days') }}</small></h4>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-success"><i class="ti ti-circle-check"></i></span>
                                        </div>
                                        <h6 class="mb-0 small text-muted text-uppercase">{{ __('Remaining') }}</h6>
                                    </div>
                                    <h4 class="mb-0">{{ (int) round($selectedEmployee->remaining_annual_leave_days) }} <small class="text-muted">{{ __('Days') }}</small></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 border-start">
                            @php
                                $earned = (int) round($selectedEmployee->earned_annual_leave_days);
                                $taken = (int) round($selectedEmployee->taken_annual_leave_days);
                                $percentage = $earned > 0 
                                    ? (int) round(($taken / $earned) * 100) 
                                    : 0;
                                $percentage = min($percentage, 100);
                            @endphp
                            <div class="d-flex justify-content-between mb-1 mt-md-0 mt-3">
                                <span class="fw-semibold">{{ __('Usage Ratio') }}</span>
                                <span class="text-muted">{{ $taken }} / {{ $earned }}</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="mt-2 text-end">
                                <span class="badge bg-label-primary">{{ $percentage }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Employee Leave Requests') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label class="form-label small">{{ __('Status') }}</label>
                            <select wire:model.live="status" class="form-select form-select-sm">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="approved">{{ __('Approved') }}</option>
                                <option value="rejected">{{ __('Rejected') }}</option>
                            </select>
                        </div>
                        @if(Auth::user()->hasRole('client') || Auth::user()->hasAnyRole(['super_admin']))
                            <div class="col-md-2">
                                <label class="form-label small">{{ __('Company') }}</label>
                                <select wire:model.live="company_id" class="form-select form-select-sm">
                                    <option value="">{{ __('All Companies') }}</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-{{ Auth::user()->hasRole('company') ? '4' : '3' }}">
                            <label class="form-label small">{{ __('Employee') }}</label>
                            <select wire:model.live="employee_id" class="form-select form-select-sm">
                                <option value="">{{ __('All Employees') }}</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-{{ Auth::user()->hasRole('company') ? '5' : '4' }}">
                            <label class="form-label small">{{ __('Search') }}</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-search ti-xs"></i></span>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="{{ __('Search employee name...') }}">
                            </div>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button wire:click="resetFilters" class="btn btn-sm btn-label-primary w-100">
                                <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Employee') }}</th>
                                    @if(Auth::user()->hasAnyRole(['client', 'super_admin'])) <th>{{ __('Company') }}</th> @endif
                                    <th>{{ __('Dates (Days)') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Decision') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveRequests as $request)
                                    <tr>
                                        <td>
                                            <div class="d-flex justify-content-start align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold">{{ $request->employee->full_name }}</span>
                                                    <small class="text-muted">{{ $request->employee->mobile_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        @if(Auth::user()->hasAnyRole(['client', 'super_admin']))
                                            <td>{{ $request->company->name }}</td>
                                        @endif
                                        <td>
                                            {{ $request->start_date->format('Y-m-d') }} <i class="ti ti-arrow-right mx-1 scaleX-n1-rtl"></i> {{ $request->end_date->format('Y-m-d') }}
                                            <span class="badge bg-label-secondary ms-1">{{ $request->total_days }} {{ __('days') }}</span>
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge bg-label-warning">{{ __('Pending') }}</span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge bg-label-success">{{ __('Approved') }}</span>
                                            @else
                                                <span class="badge bg-label-danger">{{ __('Rejected') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status !== 'pending')
                                                <small class="text-muted">
                                                    {{ __(ucfirst(str_replace('_', ' ', $request->decision_by_type))) }}
                                                    @if($request->status === 'rejected')
                                                        <i class="ti ti-info-circle text-info ms-1" title="{{ __($request->rejection_reason) }}"></i>
                                                    @endif
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <div class="d-flex gap-1">
                                                    <button wire:click="approveRequest({{ $request->id }})" class="btn btn-sm btn-icon btn-label-success" title="{{ __('Approve') }}">
                                                        <i class="ti ti-check"></i>
                                                    </button>
                                                    <button wire:click="openRejectModal({{ $request->id }})" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('Reject') }}" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                                        <i class="ti ti-x"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted small italic">{{ __('No actions') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">{{ __('No leave requests found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $leaveRequests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div wire:ignore.self class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Reject Leave Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">{{ __('Rejection Reason') }}</label>
                        <textarea wire:model="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror" rows="3" placeholder="{{ __('Explain why this request is being rejected...') }}"></textarea>
                        @error('rejection_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button wire:click="rejectRequest" class="btn btn-danger">{{ __('Confirm Rejection') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
