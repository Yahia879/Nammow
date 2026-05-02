<div>
    @section('title', __('My Advances'))

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ __('My Advances & Loans') }}</h5>
            @if(Auth::user()->canAction('request_advance'))
            <button type="button" class="btn btn-primary" wire:click="showRequestModal" data-bs-toggle="modal" data-bs-target="#advanceRequestModal" {{ !Auth::user()->employee_id ? 'disabled' : '' }}>
                <i class="ti ti-plus me-1"></i> {{ __('Request Advance') }}
            </button>
            @if(!Auth::user()->employee_id)
                <small class="text-danger ms-2">{{ __('You are not linked to an employee record.') }}</small>
            @endif
            @endif
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Requested') }}</th>
                        <th>{{ __('Approved') }}</th>
                        <th>{{ __('Installments') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($advances as $advance)
                    <tr>
                        <td>{{ $advance->created_at->format('Y-m-d') }}</td>
                        <td>{{ number_format($advance->requested_amount, 2) }}</td>
                        <td>{{ $advance->approved_amount ? number_format($advance->approved_amount, 2) : '---' }}</td>
                        <td>{{ $advance->number_of_installments ?? '---' }}</td>
                        <td>
                            <span class="badge bg-label-{{ $advance->status === 'approved' ? 'success' : ($advance->status === 'pending' ? 'warning' : 'danger') }}">
                                {{ __(ucfirst($advance->status)) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-icon" data-bs-toggle="collapse" data-bs-target="#installments-{{ $advance->id }}">
                                <i class="ti ti-chevron-down"></i>
                            </button>
                        </td>
                    </tr>
                    <tr class="collapse" id="installments-{{ $advance->id }}">
                        <td colspan="6" class="p-0">
                            <div class="bg-light p-3">
                                <h6>{{ __('Repayment Schedule') }}</h6>
                                <table class="table table-sm table-bordered bg-white">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Due Date') }}</th>
                                            <th>{{ __('Amount') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($advance->installments as $installment)
                                        <tr>
                                            <td>{{ $installment->due_date->format('Y-m-d') }}</td>
                                            <td>{{ number_format($installment->amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $installment->status === 'unpaid' ? 'secondary' : 'success' }}">
                                                    {{ __(ucfirst($installment->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">{{ __('No advances found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $advances->links() }}
        </div>
    </div>

    <!-- Request Modal -->
    <div wire:ignore.self class="modal fade" id="advanceRequestModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('New Advance Request') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="submitRequest">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('Requested Amount') }}</label>
                            <input type="number" step="0.01" class="form-control" wire:model="requested_amount">
                            @error('requested_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Reason') }}</label>
                            <textarea class="form-control" rows="3" wire:model="reason"></textarea>
                            @error('reason') <span class="text-danger small">{{ $message }}</span> @enderror
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
