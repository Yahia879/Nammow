<div>
    @section('title', __('My Leave Requests'))

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
                        <div class="col-md-2">
                            <select wire:model.live="leave_type_id" class="form-select">
                                <option value="">{{ __('All Types') }}</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ __($type->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" wire:model.live="date_from" class="form-control" placeholder="{{ __('From Date') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" wire:model.live="date_to" class="form-control" placeholder="{{ __('To Date') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('Search reason...') }}">
                        </div>
                        <div class="col-md-1">
                            <button wire:click="resetFilters" class="btn btn-outline-secondary w-100">{{ __('Reset') }}</button>
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>{{ __('Type') }}</th>
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
                                        <td>{{ __($request->leaveType->name) }}</td>
                                        <td>
                                            <span class="text-muted">{{ $request->start_date->format('Y-m-d') }}</span>
                                            <i class="ti ti-arrow-right mx-1"></i>
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
                                                <button wire:click="deleteRequest({{ $request->id }})" class="btn btn-sm btn-icon btn-label-danger" onclick="confirm('{{ __('Are you sure?') }}') || event.stopImmediatePropagation()">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No leave requests found.') }}</td>
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
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('New Leave Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="createLeaveRequest">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Leave Type') }}</label>
                            <select wire:model="new_leave_type_id" class="form-select @error('new_leave_type_id') is-invalid @enderror">
                                <option value="">{{ __('Select Type') }}</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ __($type->name) }}</option>
                                @endforeach
                            </select>
                            @error('new_leave_type_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" wire:model="new_start_date" class="form-control @error('new_start_date') is-invalid @enderror">
                                @error('new_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('End Date') }}</label>
                                <input type="date" wire:model="new_end_date" class="form-control @error('new_end_date') is-invalid @enderror">
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
</div>
