<div>
    @section('page-style')
    <style>
        .btn-tr {
            opacity: 0;
        }

        tr:hover .btn-tr {
            display: inline-block;
            opacity: 1;
        }
    </style>
    @endsection

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">{{ __('Client Management') }} /</span> {{ __('Clients') }}
    </h4>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
                <div class="col-md-4">
                    <h5 class="card-title mb-0">{{ __('Clients List') }}</h5>
                </div>
                <div class="col-md-8 text-end">
                    <button wire:click="showCreateClientModal" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#clientModal">
                        <i class="ti ti-plus me-1"></i> {{ __('Add Client') }}
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-3">
                    <label class="form-label">{{ __('Subscription Plan') }}</label>
                    <select wire:model.live="filterPlanId" class="form-select">
                        <option value="">{{ __('All Plans') }}</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ __($plan->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select wire:model.live="filterStatus" class="form-select text-capitalize">
                        <option value="">{{ __('Select Status') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                        <option value="suspended">{{ __('Suspended') }}</option>
                        <option value="expired">{{ __('Expired') }}</option>
                    </select>
                </div>
                <div class="col-md-4 offset-md-2">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input wire:model.live="searchTerm" type="text" class="form-control" placeholder="{{ __('Search (Name, Email...)') }}">
                </div>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Plan') }}</th>
                        <th>{{ __('Companies') }}</th>
                        <th>{{ __('Joined') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($clients as $client)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center user-name">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-3">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($client->name, 0, 2) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-body text-truncate">{{ $client->name }}</span>
                                    <small class="text-muted">{{ $client->slug }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-body">{{ $client->email ?? '-' }}</span>
                                <small class="text-muted">{{ $client->phone ?? '-' }}</small>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    'suspended' => 'danger',
                                    'expired' => 'warning',
                                ][$client->status] ?? 'primary';
                            @endphp
                            <span class="badge bg-label-{{ $statusClass }}">
                                {{ __($client->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-body">{{ $client->plan ? __($client->plan->name) : '-' }}</span>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-label-info">{{ $client->companies_count }}</span>
                        </td>
                        <td>
                            <span class="text-body">{{ $client->created_at->format('Y-m-d') }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <button wire:click="editClient({{ $client->id }})" type="button" class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-secondary waves-effect me-1" data-bs-toggle="modal" data-bs-target="#clientModal">
                                    <i class="ti ti-pencil ti-sm"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $client->id }})" type="button" class="btn btn-sm btn-tr rounded-pill btn-icon btn-outline-danger waves-effect" data-bs-toggle="modal" data-bs-target="#deleteClientModal">
                                    <i class="ti ti-trash ti-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="mt-4 mb-4">
                                <h5 class="mb-1">{{ __('No Clients Found') }}</h5>
                                <p class="text-muted">{{ __('Try adjusting your search or filters.') }}</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $clients->links() }}
        </div>
    </div>

    <!-- Client Modal (Create/Edit) -->
    <div wire:ignore.self class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">{{ $isEdit ? __('Edit Client') : __('Add New Client') }}</h3>
                        <p class="text-muted">{{ $isEdit ? __('Provide details to update the client.') : __('Provide details to create the client.') }}</p>
                    </div>
                    <form wire:submit.prevent="submit" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Client Name') }}</label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('فهد العتبي') }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="{{ __('info@nokhba.sa') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Phone') }}</label>
                            <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror" placeholder="{{ __('+966 5X XXX XXXX') }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                                <option value="suspended">{{ __('Suspended') }}</option>
                                <option value="expired">{{ __('Expired') }}</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Subscription Plan') }}</label>
                            <select wire:model="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                                <option value="">{{ __('Select Plan') }}</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ __($plan->name) }}</option>
                                @endforeach
                            </select>
                            @error('plan_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Password') }}</label>
                            <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="············">
                            @if($isEdit)
                                <small class="text-muted">{{ __('Leave blank to keep current password') }}</small>
                            @endif
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Confirm Password') }}</label>
                            <input wire:model="password_confirmation" type="password" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="············">
                            @error('password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary me-sm-3 me-1">{{ __('Submit') }}</button>
                            <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">{{ __('Cancel') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    @include('_partials/_modals/modal-delete-client')
</div>
