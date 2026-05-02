<div>
    @section('title', __('My Salary Details'))

    <!-- Month Navigation -->
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center">
                <button type="button" wire:click="previousMonth" class="btn btn-sm btn-outline-primary shadow-none">
                    <i class="ti ti-chevron-left me-1"></i> {{ __('Previous Month') }}
                </button>
                <h4 class="mb-0 fw-bold text-primary mx-3">{{ $monthName }}</h4>
                <button type="button" wire:click="nextMonth" class="btn btn-sm btn-outline-primary shadow-none">
                    {{ __('Next Month') }} <i class="ti ti-chevron-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Monthly Net Result -->
        <div class="col-12">
            <div class="card bg-primary text-white border-0 shadow-lg">
                <div class="card-body text-center p-4">
                    <label class="text-uppercase small fw-bold opacity-75 d-block mb-1">{{ __('Net Salary for') }} {{ $monthName }}</label>
                    <h1 class="fw-bold mb-1 text-white display-5">{{ number_format($netResult, 2) }}</h1>
                    <small class="opacity-75 italic">{{ __('Final amount after monthly deductions and installments') }}</small>
                </div>
            </div>
        </div>

        <!-- Left Column: Current Structure Breakdown -->
        <div class="col-xl-6 col-12">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header border-bottom">
                    <h5 class="mb-0 fw-bold">{{ __('Current Salary Structure') }}</h5>
                </div>
                <div class="card-body py-4">
                    @if($salary)
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td class="ps-0 py-2 fw-semibold"><i class="ti ti-wallet me-2"></i> {{ __('Basic Salary') }}</td>
                                    <td class="pe-0 py-2 text-end fw-bold">{{ number_format($salary->amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0 py-2 fw-semibold"><i class="ti ti-plus me-2 text-success"></i> {{ __('Total Allowances') }}</td>
                                    <td class="pe-0 py-2 text-end text-success fw-bold">+ {{ number_format($salary->allowances, 2) }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td colspan="2" class="p-0"></td>
                                </tr>
                                <tr>
                                    <td class="ps-0 py-2 fw-semibold"><i class="ti ti-minus me-2 text-danger"></i> {{ __('Income Tax') }}</td>
                                    <td class="pe-0 py-2 text-end text-danger fw-bold">- {{ number_format($salary->tax, 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-0 py-2 fw-semibold"><i class="ti ti-shield-check me-2 text-danger"></i> {{ __('Social Insurance') }}</td>
                                    <td class="pe-0 py-2 text-end text-danger fw-bold">- {{ number_format($salary->insurance, 2) }}</td>
                                </tr>
                                <tr class="bg-label-secondary rounded">
                                    <td class="ps-3 py-3 fw-bold h6 mb-0">{{ __('Total Standard Net') }}</td>
                                    <td class="pe-3 py-3 text-end fw-bold h6 mb-0">{{ number_format($salary->net_salary, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5 text-muted small">
                        <i class="ti ti-info-circle ti-md mb-2"></i><br>
                        {{ __('No salary data found.') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Salary History -->
        <div class="col-xl-6 col-12">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header border-bottom">
                    <h5 class="mb-0 fw-bold">{{ __('Salary History') }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="px-3">{{ __('Effective Date') }}</th>
                                    <th class="text-end px-3">{{ __('Basic') }}</th>
                                    <th class="text-end px-3">{{ __('Net') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salaryHistory as $history)
                                <tr>
                                    <td class="py-3 px-3">{{ $history->effective_date ? $history->effective_date->format('d M, Y') : '--' }}</td>
                                    <td class="py-3 px-3 text-end fw-bold">{{ number_format($history->amount, 2) }}</td>
                                    <td class="py-3 px-3 text-end text-success fw-bold">{{ number_format($history->net_salary, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">{{ __('No history available.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-2">
                    {{ $salaryHistory->links() }}
                </div>
            </div>
        </div>

        <!-- Bottom Row: Variable Monthly Adjustments -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom">
                    <h5 class="mb-0 fw-bold">{{ __('Monthly Variable Details') }} ({{ $monthName }})</h5>
                </div>
                <div class="card-body py-4">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="text-uppercase small fw-bold text-muted mb-3">{{ __('Discounts & Penalties') }}</h6>
                            <ul class="list-group list-group-flush">
                                @forelse($discounts as $discount)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-3 border-top-0">
                                    <span>{{ $discount->reason }} <small class="text-muted d-block">{{ $discount->date }}</small></span>
                                    <span class="text-danger fw-bold">- {{ number_format($discount->amount, 2) }}</span>
                                </li>
                                @empty
                                <li class="list-group-item ps-0 text-muted small italic border-0">{{ __('No penalties.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6 ps-md-4">
                            <h6 class="text-uppercase small fw-bold text-muted mb-3">{{ __('Loan Installments') }}</h6>
                            <ul class="list-group list-group-flush">
                                @forelse($installments as $installment)
                                <li class="list-group-item d-flex justify-content-between align-items-center ps-0 pe-0 border-top-0">
                                    <span>{{ __('Advance Installment') }} #{{ $installment->advance->id }} <small class="text-muted d-block">{{ $installment->due_date->format('Y-m-d') }}</small></span>
                                    <span class="text-danger fw-bold">- {{ number_format($installment->amount, 2) }}</span>
                                </li>
                                @empty
                                <li class="list-group-item ps-0 text-muted small italic border-0">{{ __('No installments.') }}</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer border-top bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-muted">{{ __('Total Deductions this month:') }}</span>
                        <span class="text-danger fw-bold h5 mb-0">- {{ number_format($totalDiscounts + $totalInstallments, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
