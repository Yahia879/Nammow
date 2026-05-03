<div>
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">{{ __('client.company_management') }} /</span> {{ __('client.companies') }}
    </h4>

    <div class="card">
        <div class="card-header border-bottom">
            <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
                <div class="col-md-4">
                    <h5 class="card-title mb-0">{{ __('Companies List') }}</h5>
                </div>
                <div class="col-md-8 text-end">
                    <button wire:click="showCreateCompanyModal" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#companyModal">
                        <i class="ti ti-plus me-1"></i> {{ __('Add Company') }}
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
                <div class="col-md-4 offset-md-8">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input wire:model.live="searchTerm" type="text" class="form-control" placeholder="{{ __('Search (Name, Email...)') }}">
                </div>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Company Name') }}</th>
                        <th>{{ __('company.cr_number') }}</th>
                        <th>{{ __('company.unified_number') }}</th>
                        <th>{{ __('company.approval_expiry_date') }}</th>
                        <th>{{ __('Managers') }}</th>
                        <th>{{ __('Created At') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($companies as $company)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-3">
                                        @if($company->cr_image && Str::endsWith($company->cr_image, ['.jpg', '.jpeg', '.png', '.webp']))
                                            <img src="{{ asset('storage/' . $company->cr_image) }}" alt="CR Image" class="rounded-circle">
                                        @else
                                            <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($company->name, 0, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold text-body text-truncate">{{ $company->name }}</span>
                                    <small class="text-muted">{{ $company->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-body">{{ $company->cr_number }}</span>
                        </td>
                        <td>
                            <span class="text-body">{{ $company->unified_number }}</span>
                        </td>
                        <td>
                            @php
                                $expiryDate = \Carbon\Carbon::parse($company->attestation_expiry_date);
                                $isExpired = $expiryDate->isPast();
                                $daysRemaining = now()->diffInDays($expiryDate, false);
                                $isExpiringSoon = !$isExpired && $daysRemaining <= 30;
                                
                                $badgeClass = 'bg-label-success';
                                if ($isExpired) {
                                    $badgeClass = 'bg-label-danger';
                                } elseif ($isExpiringSoon) {
                                    $badgeClass = 'bg-label-warning';
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                {{ $company->attestation_expiry_date }}
                            </span>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-label-info">{{ $company->managers_count }}</span>
                        </td>
                        <td>
                            <span class="text-body">{{ $company->created_at->format('Y-m-d') }}</span>
                        </td>
                        <td>
                            <div class="d-inline-block text-nowrap">
                                <button class="btn btn-sm btn-icon btn-label-secondary rounded-pill waves-effect dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical ti-sm"></i></button>
                                <div class="dropdown-menu dropdown-menu-end m-0">
                                    <a href="javascript:void(0);" wire:click="editCompany({{ $company->id }})" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#companyModal">{{ __('Edit') }}</a>
                                    <a href="javascript:void(0);" wire:click="confirmDelete({{ $company->id }})" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteCompanyModal">{{ __('Delete') }}</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="mt-4 mb-4">
                                <h5 class="mb-1">{{ __('No Companies Found') }}</h5>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $companies->links() }}
        </div>
    </div>

    <!-- Delete Company Modal -->
    <div wire:ignore.self class="modal fade" id="deleteCompanyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Delete Company') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="ti ti-alert-triangle text-warning ti-xl mb-3"></i>
                        <p class="h5">{{ __('Are you sure you want to deactivate this company?') }}</p>
                        <p class="text-muted">{{ __('This action will deactivate the company (soft delete).') }}</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button wire:click="deleteCompany({{ $confirmedId }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Confirm Delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Company Modal -->
    <div wire:ignore.self class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">{{ $isEdit ? __('Edit Company') : __('Add New Company') }}</h3>
                        <p class="text-muted">{{ $isEdit ? __('Provide details to update the company.') : __('Provide details to create a new company.') }}</p>
                    </div>

                    <div x-data="{ activeTab: @entangle('activeTab') }" class="nav-align-top mb-4">
                        <ul class="nav nav-tabs justify-content-center mb-3" role="tablist">
                            <li class="nav-item">
                                <button x-on:click="activeTab = 'company-info'" type="button" class="nav-link" :class="{ 'active': activeTab === 'company-info' }" role="tab">
                                    <i class="ti ti-building me-1"></i> {{ __('Company Info') }}
                                    @if($errors->has('name') || $errors->has('owner_name') || $errors->has('cr_number') || $errors->has('unified_number') || $errors->has('attestation_date') || $errors->has('attestation_expiry_date') || $errors->has('email') || $errors->has('phone') || $errors->has('address') || $errors->has('cr_image'))
                                        <span class="badge badge-dot bg-danger"></span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item">
                                <button x-on:click="activeTab = 'managers'" type="button" class="nav-link" :class="{ 'active': activeTab === 'managers' }" role="tab">
                                    <i class="ti ti-users me-1"></i> {{ __('Company Managers') }}
                                    @if($errors->has('managers.*'))
                                        <span class="badge badge-dot bg-danger"></span>
                                    @endif
                                </button>
                            </li>
                        </ul>
                        <form wire:submit.prevent="submit">
                            <div class="tab-content">
                                <!-- Company Info Tab -->
                                <div class="tab-pane fade" :class="{ 'show active': activeTab === 'company-info' }" id="navs-company-info" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Company Name') }} <span class="text-danger">*</span></label>
                                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Company Name') }}">
                                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('company.owner_name') }} <span class="text-danger">*</span></label>
                                            <input wire:model="owner_name" type="text" class="form-control @error('owner_name') is-invalid @enderror" placeholder="{{ __('company.owner_name') }}">
                                            @error('owner_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('company.cr_number') }} <span class="text-danger">*</span></label>
                                            <input wire:model="cr_number" type="text" class="form-control @error('cr_number') is-invalid @enderror" placeholder="1010XXXXXX">
                                            @error('cr_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('company.unified_number') }} <span class="text-danger">*</span></label>
                                            <input wire:model="unified_number" type="text" class="form-control @error('unified_number') is-invalid @enderror" placeholder="700XXXXXXX">
                                            @error('unified_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('company.approval_date') }} <span class="text-danger">*</span></label>
                                            <input wire:model="attestation_date" type="date" class="form-control @error('attestation_date') is-invalid @enderror">
                                            @error('attestation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('company.approval_expiry_date') }} <span class="text-danger">*</span></label>
                                            <input wire:model="attestation_expiry_date" type="date" class="form-control @error('attestation_expiry_date') is-invalid @enderror">
                                            @error('attestation_expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">{{ __('Email') }} <span class="text-danger">*</span></label>
                                            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="info@company.com">
                                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">{{ __('company.phone') }} <span class="text-danger">*</span></label>
                                            <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror" placeholder="+966 5X XXX XXXX">
                                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">{{ __('Address') }}</label>
                                            <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" placeholder="{{ __('Address') }}"></textarea>
                                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">{{ __('company.cr_image') }} <span class="text-danger">*</span></label>
                                            <input wire:model="cr_image" type="file" class="form-control @error('cr_image') is-invalid @enderror" accept="image/png, image/jpeg, image/webp, application/pdf">
                                            <div class="form-text">{{ __('company.allowed_formats') }}</div>
                                            @error('cr_image') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                            @if ($cr_image && !$errors->has('cr_image'))
                                                <div class="mt-2 text-center">
                                                    @if(Str::endsWith($cr_image->getClientOriginalName(), '.pdf'))
                                                        <i class="ti ti-file-description ti-xl text-primary"></i>
                                                        <p>{{ $cr_image->getClientOriginalName() }}</p>
                                                    @else
                                                        <img src="{{ $cr_image->temporaryUrl() }}" width="100" class="rounded border">
                                                    @endif
                                                </div>
                                            @elseif($existingCrImage)
                                                <div class="mt-2 text-center">
                                                    @if(Str::endsWith($existingCrImage, '.pdf'))
                                                        <a href="{{ asset('storage/' . $existingCrImage) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="ti ti-file-description me-1"></i> {{ __('View Current CR Document') }}
                                                        </a>
                                                    @else
                                                        <img src="{{ asset('storage/' . $existingCrImage) }}" width="100" class="rounded border">
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Company Managers Tab -->
                                <div class="tab-pane fade" :class="{ 'show active': activeTab === 'managers' }" id="navs-managers" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">{{ __('Managers List') }}</h5>
                                        <button wire:click="addManager" type="button" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-plus me-1"></i> {{ __('Add Manager') }}
                                        </button>
                                    </div>

                                    @foreach($managers as $index => $manager)
                                    <div class="card border mb-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0">{{ __('Manager') }} #{{ $index + 1 }} @if(isset($manager['is_existing']) && $manager['is_existing']) <span class="badge bg-label-info ms-2">{{ __('Existing') }}</span> @endif</h6>
                                                @if((!isset($manager['is_existing']) || !$manager['is_existing']) && count($managers) > 1)
                                                <button wire:click="removeManager({{ $index }})" type="button" class="btn btn-sm btn-label-danger btn-icon">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                                @endif
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label">{{ __('Manager Name') }}</label>
                                                    <input wire:model="managers.{{ $index }}.name" type="text" class="form-control @error('managers.' . $index . '.name') is-invalid @enderror" placeholder="{{ __('Manager Name') }}" {{ (isset($manager['is_existing']) && $manager['is_existing']) ? 'readonly' : '' }}>
                                                    @error('managers.' . $index . '.name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Manager Email') }}</label>
                                                    <input wire:model="managers.{{ $index }}.email" type="email" class="form-control @error('managers.' . $index . '.email') is-invalid @enderror" placeholder="manager@company.com" {{ (isset($manager['is_existing']) && $manager['is_existing']) ? 'readonly' : '' }}>
                                                    @error('managers.' . $index . '.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Manager Phone') }}</label>
                                                    <input wire:model="managers.{{ $index }}.phone" type="text" class="form-control @error('managers.' . $index . '.phone') is-invalid @enderror" placeholder="+966 5X XXX XXXX" {{ (isset($manager['is_existing']) && $manager['is_existing']) ? 'readonly' : '' }}>
                                                    @error('managers.' . $index . '.phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                @if(!isset($manager['is_existing']) || !$manager['is_existing'])
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Password') }}</label>
                                                    <input wire:model="managers.{{ $index }}.password" type="password" class="form-control @error('managers.' . $index . '.password') is-invalid @enderror" placeholder="············">
                                                    @error('managers.' . $index . '.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Confirm Password') }}</label>
                                                    <input wire:model="managers.{{ $index }}.password_confirmation" type="password" class="form-control" placeholder="············">
                                                </div>
                                                @else
                                                <div class="col-md-6">
                                                    <label class="form-label">{{ __('Status') }}</label>
                                                    <input wire:model="managers.{{ $index }}.status" type="text" class="form-control" readonly>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
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
    </div>
</div>
