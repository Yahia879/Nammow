<div>
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">{{ __('client.company_management') }} /</span> {{ __('Company Managers') }}
    </h4>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
                <div class="col-md-4">
                    <h5 class="card-title mb-0">{{ __('Managers List') }}</h5>
                </div>
                <div class="col-md-8 text-end">
                    <button wire:click="showCreateManagerModal" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#managerModal">
                        <i class="ti ti-plus me-1"></i> {{ __('Add Manager') }}
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-4">
                    <label class="form-label">{{ __('الشركة') }}</label>
                    <select wire:model.live="filterCompany" class="form-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 offset-md-4">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input wire:model.live="searchTerm" type="text" class="form-control" placeholder="{{ __('Search (Name, Email...)') }}">
                </div>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Manager Name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('company.phone') }}</th>
                        <th>{{ __('Company Name') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($managers as $manager)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-3">
                                        @if($manager->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $manager->user->profile_photo_path) }}" alt="Avatar" class="rounded-circle">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-info">{{ substr($manager->user->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-body text-truncate">{{ $manager->user->name }}</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-body">{{ $manager->user->email }}</span>
                        </td>
                        <td>
                            <span class="text-body">{{ $manager->user->mobile ?: '-' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-label-primary" title="{{ $manager->companies->pluck('name')->implode('، ') }}">
                                {{ $manager->companies->take(3)->pluck('name')->implode('، ') }}{{ $manager->companies->count() > 3 ? '...' : '' }}
                            </span>
                        </td>
                        <td>
                            <span class="text-body">{{ $manager->created_at->format('Y-m-d') }}</span>
                        </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon btn-label-secondary rounded-pill waves-effect dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical ti-sm"></i></button>
                                <div class="dropdown-menu dropdown-menu-end m-0">
                                    <a href="javascript:void(0);" wire:click="editManager({{ $manager->id }})" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#managerModal">{{ __('Edit') }}</a>
                                    <a href="javascript:void(0);" wire:click="confirmDelete({{ $manager->id }})" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteManagerModal">{{ __('Delete') }}</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="mt-4 mb-4">
                                <h5 class="mb-1">{{ __('لا توجد بيانات حالياً / No data available yet') }}</h5>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $managers->links() }}
        </div>
    </div>

    <!-- Create/Edit Manager Modal -->
    <div wire:ignore.self class="modal fade" id="managerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">{{ $isEdit ? __('company.edit_manager') : __('company.add_manager') }}</h3>
                        <p class="text-muted">{{ __('company.add_manager_description') }}</p>
                    </div>

                    <form wire:submit.prevent="{{ $isEdit ? 'update' : 'store' }}">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">{{ __('Full Name') }} <span class="text-danger">*</span></label>
                                <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Full Name') }}">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">{{ __('company.company') }} <span class="text-danger">*</span></label>
                                <div class="border rounded p-3">
                                    <input wire:model.live="companySearch" type="text" class="form-control mb-2" placeholder="{{ __('Search Company...') }}">
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        @foreach($companies as $company)
                                            <div class="form-check mb-2">
                                                <input wire:model="selected_companies" class="form-check-input" type="checkbox" value="{{ $company->id }}" id="company_{{ $company->id }}">
                                                <label class="form-check-label" for="company_{{ $company->id }}">
                                                    {{ $company->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                        @if($companies->isEmpty())
                                            <p class="text-muted small mb-0">{{ __('No companies found.') }}</p>
                                        @endif
                                    </div>
                                </div>
                                @error('selected_companies') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="manager@company.com">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('company.phone') }}</label>
                                <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror" placeholder="+966 5X XXX XXXX">
                                @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Password') }} @if(!$isEdit) <span class="text-danger">*</span> @endif</label>
                                <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="············">
                                @if($isEdit) <small class="text-muted">{{ __('Leave blank to keep current password') }}</small> @endif
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Confirm Password') }} @if(!$isEdit) <span class="text-danger">*</span> @endif</label>
                                <input wire:model="password_confirmation" type="password" class="form-control" placeholder="············">
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">{{ __('Submit') }}</button>
                                <button type="reset" class="btn btn-label-secondary" data-bs-dismiss="modal" aria-label="Close">{{ __('Cancel') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Manager Modal -->
    <div wire:ignore.self class="modal fade" id="deleteManagerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Delete Manager') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="ti ti-alert-triangle text-warning ti-xl mb-3"></i>
                        <p class="h5">{{ __('Are you sure you want to delete this manager?') }}</p>
                        <p class="text-muted">{{ __('This action will delete the manager record and their user account.') }}</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button wire:click="deleteManager({{ $confirmedId }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Confirm Delete') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>
