<div>
    @section('vendor-style')
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/animate-css/animate.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
    @endsection

    @section('vendor-script')
        <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
    @endsection

    @section('title', __('Manage Holidays'))

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Holidays') }}</h5>
                    @if(!Auth::user()->hasAnyRole(['super_admin']))
                        <button wire:click="showCreateModal" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#holidayModal">
                            <i class="ti ti-plus me-1"></i> {{ __('New Holiday') }}
                        </button>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        @if(Auth::user()->hasRole('client') || Auth::user()->hasAnyRole(['super_admin']))
                            <div class="col-md-2">
                                <label class="form-label small">{{ __('Company') }}</label>
                                <select wire:model.live="company_id" class="form-select form-select-sm">
                                    <option value="">{{ __('All Companies') }}</option>
                                    @foreach($availableCompanies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-2">
                            <label class="form-label small">{{ __('Scope') }}</label>
                            <select wire:model.live="scope" class="form-select form-select-sm">
                                <option value="">{{ __('All Scopes') }}</option>
                                <option value="all">{{ __('All Companies') }}</option>
                                <option value="selected">{{ __('Selected Companies') }}</option>
                                <option value="single">{{ __('Single Company') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ __('Created By') }}</label>
                            <select wire:model.live="created_by_type" class="form-select form-select-sm">
                                <option value="">{{ __('All') }}</option>
                                <option value="client">{{ __('Client') }}</option>
                                <option value="company_manager">{{ __('Company Manager') }}</option>
                                <option value="super_admin">{{ __('Super Admin') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ __('From Date') }}</label>
                            <input type="date" wire:model.live="date_from" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ __('To Date') }}</label>
                            <input type="date" wire:model.live="date_to" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button wire:click="resetFilters" class="btn btn-sm btn-outline-secondary w-100">{{ __('Reset') }}</button>
                        </div>
                    </div>

                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Dates') }}</th>
                                    <th>{{ __('Scope') }}</th>
                                    <th>{{ __('Created By') }}</th>
                                    <th>{{ __('Companies') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($holidays as $holiday)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ __($holiday->title) }}</span>
                                            @if($holiday->description)
                                                <i class="ti ti-info-circle text-info ms-1" title="{{ __($holiday->description) }}"></i>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $holiday->start_date->format('Y-m-d') }} {{ __('to') }} {{ $holiday->end_date->format('Y-m-d') }}
                                        </td>
                                        <td>
                                            @if($holiday->scope === 'all')
                                                <span class="badge bg-label-primary">{{ __('All Companies') }}</span>
                                            @elseif($holiday->scope === 'selected')
                                                <span class="badge bg-label-info">{{ __('Selected') }}</span>
                                            @else
                                                <span class="badge bg-label-secondary">{{ __('Single') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ __(ucfirst(str_replace('_', ' ', $holiday->created_by_type))) }}</small>
                                        </td>
                                        <td>
                                            @if($holiday->scope === 'all')
                                                <span class="text-muted small">{{ __('All companies of client') }}</span>
                                            @else
                                                @foreach($holiday->companies as $co)
                                                    <span class="badge bg-label-dark small">{{ $co->name }}</span>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $user = Auth::user();
                                                $canModify = false;
                                                
                                                if ($user->hasAnyRole(['super_admin'])) {
                                                    $canModify = false; // Super admin can only view
                                                } elseif ($user->hasRole('company')) {
                                                    $canModify = $holiday->company_manager_id == $user->id;
                                                } elseif ($user->hasRole('client')) {
                                                    $canModify = $holiday->created_by_type === 'client' && $holiday->client_id == $user->client_id;
                                                }
                                            @endphp

                                            @if($canModify)
                                                <div class="d-flex gap-1">
                                                    <button wire:click="showEditModal({{ $holiday->id }})" class="btn btn-sm btn-icon btn-label-primary" title="{{ __('Edit') }}" data-bs-toggle="modal" data-bs-target="#holidayModal">
                                                        <i class="ti ti-pencil"></i>
                                                    </button>
                                                    <button wire:click="confirmDelete({{ $holiday->id }})" class="btn btn-sm btn-icon btn-label-danger" title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-muted small italic">{{ __('Read-only') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ __('No holidays found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $holidays->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Holiday Modal -->
    <div wire:ignore.self class="modal fade" id="holidayModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? __('Edit Holiday') : __('Create Holiday') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="saveHoliday">
                    <div class="modal-body">
                        @if(Auth::user()->hasAnyRole(['super_admin']))
                            <div class="mb-3">
                                <label class="form-label">{{ __('Client') }}</label>
                                <select wire:model.live="client_id_form" class="form-select @error('client_id_form') is-invalid @enderror">
                                    <option value="">{{ __('Select Client') }}</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                                @error('client_id_form') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">{{ __('Title') }}</label>
                            <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror" placeholder="{{ __('Holiday Title') }}">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Description (Optional)') }}</label>
                            <textarea wire:model="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('Start Date') }}</label>
                                <input type="date" wire:model="start_date" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ __('End Date') }}</label>
                                <input type="date" wire:model="end_date" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @if(Auth::user()->hasAnyRole(['client', 'super_admin']))
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input wire:model.live="apply_to_all" class="form-check-input" type="checkbox" id="applyToAll">
                                    <label class="form-check-label fw-bold" for="applyToAll">{{ __('Apply to all companies') }}</label>
                                </div>
                                <small class="text-muted">{{ __('Enable this to make the holiday global for all companies under the selected client (or all companies if no client selected).') }}</small>
                            </div>
                        @endif

                        @if(!$apply_to_all)
                            <div class="mb-3">
                                <label class="form-label">{{ __('Target Companies') }}</label>
                                <div class="row g-2">
                                    @foreach($availableCompanies->when(Auth::user()->hasAnyRole(['super_admin']) && $client_id_form, function($q) { return $q->where('client_id', $client_id_form); }) as $company)
                                        <div class="col-md-4">
                                            <div class="form-check">
                                                <input wire:model="selected_companies" class="form-check-input" type="checkbox" value="{{ $company->id }}" id="co_{{ $company->id }}" 
                                                    {{ Auth::user()->hasRole('company') ? 'disabled' : '' }}>
                                                <label class="form-check-label" for="co_{{ $company->id }}">{{ $company->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selected_companies') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save Holiday') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
