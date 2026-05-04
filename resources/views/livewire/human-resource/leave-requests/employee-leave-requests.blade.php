<div>
    @section('title', __('My Leave Requests'))

    @section('vendor-style')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    @endsection

    @section('vendor-script')
        <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    @endsection

    @if(isset($employee))
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="card-title mb-4">{{ __('Annual Leave Summary') }} ({{ now()->year }})</h5>
                            <div class="row">
                                <div class="col-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="ti ti-calendar-event"></i></span>
                                        </div>
                                        <h6 class="mb-0">{{ __('Earned') }}</h6>
                                    </div>
                                    <h4 class="mb-0">{{ (int) round($employee->earned_annual_leave_days) }} <small class="text-muted">{{ __('Days') }}</small></h4>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-warning"><i class="ti ti-plane-departure"></i></span>
                                        </div>
                                        <h6 class="mb-0">{{ __('Taken') }}</h6>
                                    </div>
                                    <h4 class="mb-0">{{ (int) round($employee->taken_annual_leave_days) }} <small class="text-muted">{{ __('Days') }}</small></h4>
                                </div>
                                <div class="col-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded bg-label-success"><i class="ti ti-circle-check"></i></span>
                                        </div>
                                        <h6 class="mb-0">{{ __('Remaining') }}</h6>
                                    </div>
                                    <h4 class="mb-0">{{ (int) round($employee->remaining_annual_leave_days) }} <small class="text-muted">{{ __('Days') }}</small></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 border-start">
                            @php
                                $earned = (int) round($employee->earned_annual_leave_days);
                                $taken = (int) round($employee->taken_annual_leave_days);
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('My Leave Requests') }}</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLeaveModal">
                        <i class="ti ti-plus me-1"></i> {{ __('New Request') }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <select wire:model.live="status" class="form-select">
                                <option value="">{{ __('All Statuses') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="approved">{{ __('Approved') }}</option>
                                <option value="rejected">{{ __('Rejected') }}</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" wire:model.live="date_from" class="form-control" placeholder="{{ __('From Date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" wire:model.live="date_to" class="form-control" placeholder="{{ __('To Date') }}">
                        </div>
                        <div class="col-md-3">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="ti ti-search ti-xs"></i></span>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('Search reason...') }}">
                            </div>
                        </div>
                        <div class="col-md-1">
                            <button wire:click="resetFilters" class="btn btn-label-primary w-100">
                                <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Dates') }}</th>
                                    <th>{{ __('Days') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Decision Info') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveRequests as $request)
                                    <tr>
                                        <td>
                                            <span class="text-muted">{{ $request->start_date->format('Y-m-d') }}</span>
                                            <i class="ti ti-arrow-right mx-1 scaleX-n1-rtl"></i>
                                            <span class="text-muted">{{ $request->end_date->format('Y-m-d') }}</span>
                                        </td>
                                        <td>{{ $request->total_days }}</td>
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
                                                    {{ __('By') }}: {{ __(ucfirst(str_replace('_', ' ', $request->decision_by_type))) }} ({{ $request->decision_at->diffForHumans() }})
                                                    @if($request->status === 'rejected')
                                                        <br><span class="text-danger">{{ __('Reason') }}: {{ __($request->rejection_reason) }}</span>
                                                    @endif
                                                </small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <button type="button" class="btn btn-sm btn-icon btn-label-danger" 
                                                    onclick="confirmDelete({{ $request->id }})">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">{{ __('No leave requests found.') }}</td>
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

    <!-- Create Modal -->
    <div wire:ignore.self class="modal fade" id="createLeaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('New Leave Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="createLeaveRequest">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" wire:model="new_start_date" min="{{ $minDate }}" max="{{ $maxDate }}" class="form-control @error('new_start_date') is-invalid @enderror">
                                @error('new_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('End Date') }}</label>
                                <input type="date" wire:model="new_end_date" min="{{ $minDate }}" max="{{ $maxDate }}" class="form-control @error('new_end_date') is-invalid @enderror">
                                @error('new_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Reason') }}</label>
                            <textarea wire:model="new_reason" class="form-control @error('new_reason') is-invalid @enderror" rows="3"></textarea>
                            @error('new_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Submit Request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('custom-scripts')
        <script>
            function confirmDelete(id) {
                Swal.fire({
                    title: "{{ __('Are you sure?') }}",
                    text: "{{ __('You won\'t be able to revert this!') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('Yes, delete it!') }}",
                    cancelButtonText: "{{ __('Cancel') }}",
                    customClass: {
                        confirmButton: 'btn btn-danger me-1',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('deleteRequest', id);
                    }
                });
            }
        </script>
    @endpush
</div>
