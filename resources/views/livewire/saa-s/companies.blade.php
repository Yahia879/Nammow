<div>
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">{{ __('Client Management') }} /</span> {{ __('Companies') }}
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
                <div class="col-md-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select wire:model.live="filterStatus" class="form-select text-capitalize">
                        <option value="">{{ __('Show All') }}</option>
                        <option value="active">{{ __('Active') }}</option>
                        <option value="inactive">{{ __('Inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-4 offset-md-5">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input wire:model.live="searchTerm" type="text" class="form-control" placeholder="{{ __('Search (Name, Email...)') }}">
                </div>
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('Company') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Address') }}</th>
                        <th>{{ __('Employees') }}</th>
                        <th>{{ __('Joined') }}</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($companies as $company)
                    <tr>
                        <td>
                            <div class="d-flex justify-content-start align-items-center">
                                <div class="avatar-wrapper">
                                    <div class="avatar avatar-sm me-3">
                                        @if($company->logo)
                                            <img src="{{ asset('storage/' . $company->logo) }}" alt="Logo" class="rounded-circle">
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
                            @php
                                $statusClass = [
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                ][$company->status] ?? 'primary';
                            @endphp
                            <span class="badge bg-label-{{ $statusClass }}">
                                {{ __($company->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-body">{{ $company->address ?? '-' }}</span>
                                <small class="text-muted">{{ $company->phone }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-label-info">{{ $company->employees_count ?? $company->employees()->count() }}</span>
                        </td>
                        <td>
                            <span class="text-body">{{ $company->created_at->format('Y-m-d') }}</span>
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

    <!-- Create Company Modal -->
    <div wire:ignore.self class="modal fade" id="companyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3 p-md-5">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">{{ __('Add New Company') }}</h3>
                        <p class="text-muted">{{ __('Provide details to create a new company.') }}</p>
                    </div>
                    <form wire:submit.prevent="store" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Company Name') }}</label>
                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('Company Name') }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" placeholder="info@company.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">{{ __('Phone') }}</label>
                            <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror" placeholder="+966 5X XXX XXXX">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Address') }}</label>
                            <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" placeholder="{{ __('Address') }}"></textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Status') }}</label>
                            <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('Logo') }}</label>
                            <input wire:model="logo" type="file" class="form-control @error('logo') is-invalid @enderror" accept="image/png, image/jpeg, image/webp">
                            <div class="form-text">{{ __('Allowed formats: JPG, PNG, WEBP') }}</div>
                            @error('logo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            @if ($logo && !$errors->has('logo'))
                                <div class="mt-2 text-center">
                                    <img src="{{ $logo->temporaryUrl() }}" width="100" class="rounded border">
                                </div>
                            @endif
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
