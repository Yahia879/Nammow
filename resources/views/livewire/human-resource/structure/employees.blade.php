<div>

@php
  $configData = Helper::appClasses();
@endphp

@section('title', 'Employees - Structure')

@section('page-style')
  <style>
    .btn-tr {
      opacity: 0;
      transition: opacity 0.15s ease-in-out;
    }

    tr:hover .btn-tr {
      opacity: 1;
    }

    .table th {
      text-transform: uppercase;
      font-size: 0.75rem;
      letter-spacing: 1px;
    }

    .table td {
      vertical-align: middle;
      padding-top: 1rem !important;
      padding-bottom: 1rem !important;
    }

    .text-heading {
      color: #5d596c !important;
    }
  </style>
@endsection

<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">{{ __('Structure') }} /</span> {{ __('Employees') }}
</h4>

<div class="card">
  <div class="card-header border-bottom">
    <div class="d-flex justify-content-between align-items-center row pb-2 gap-3 gap-md-0">
      <div class="col-md-4">
        <h5 class="card-title mb-0">{{ __('Employees') }}</h5>
      </div>
      <div class="col-md-8 text-end">
        <button wire:click='showCreateEmployeeModal' type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#employeeModal">
          <i class="ti ti-plus me-1"></i> {{ __('Add New Employee') }}
        </button>
      </div>
    </div>
    <div class="d-flex justify-content-between align-items-center row py-3 gap-3 gap-md-0">
      <div class="col-md-4 offset-md-8">
        <label class="form-label">{{ __('Search') }}</label>
        <div class="input-group input-group-merge">
          <span class="input-group-text"><i class="ti ti-search"></i></span>
          <input wire:model.live="searchTerm" autofocus type="text" class="form-control" placeholder="{{ __('Search (ID, Name...)') }}">
        </div>
      </div>
    </div>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="border-top">
        <tr>
          <th>{{ __('Full Name') }}</th>
          <th class="text-center">{{ __('Gender') }}</th>
          <th class="text-end">{{ __('Salary') }}</th>
          <th class="text-center">{{ __('Join Date') }}</th>
          <th class="text-center">{{ __('Leave Days') }}</th>
          <th class="text-end">{{ __('Mobile') }}</th>
          <th class="text-center">{{ __('Status') }}</th>
          <th class="text-center">{{ __('Actions') }}</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse($employees as $employee)
        <tr>
          <td>
            <span class="fw-bold text-heading">{{ $employee->full_name }}</span>
          </td>
          <td class="text-center">
            <span class="text-muted">{{ $employee->gender ? 'ذكر' : 'أنثى' }}</span>
          </td>
          <td class="text-end">
            <span class="text-success fw-bold">$</span>
            <span class="text-heading fw-semibold">{{ number_format($employee->basic_salary, 0, '.', ',') }}</span>
          </td>
          <td class="text-center"><span class="text-muted">{{ $employee->join_date }}</span></td>
          <td class="text-center">
            <span class="badge bg-label-secondary rounded-pill">{{ $employee->max_leave_allowed ?? 0 }}</span>
          </td>
          <td class="text-end" style="direction: ltr">
            <small class="text-muted">{{ $employee->mobile_number }}</small>
          </td>
          <td class="text-center">
            @if ($employee->is_active)
              <span class="badge bg-label-success px-3">{{ __('Active') }}</span>
            @else
              <span class="badge bg-label-danger px-3">{{ __('Out of work') }}</span>
            @endif
          </td>
          <td class="text-center">
            <div class="d-inline-block text-nowrap">
              <button class="btn btn-sm btn-icon btn-label-secondary rounded-pill waves-effect dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="ti ti-dots-vertical ti-sm"></i>
              </button>
              <div class="dropdown-menu dropdown-menu-end m-0">
                <a href="javascript:void(0);" wire:click='showEditEmployeeModal({{ $employee->id }})' data-bs-toggle="modal" data-bs-target="#employeeModal" class="dropdown-item">
                  <i class="ti ti-pencil me-2"></i> {{ __('Edit') }}
                </a>
                <a href="javascript:void(0);" wire:click='confirmDeleteEmployee({{ $employee->id }})' data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal" class="dropdown-item text-danger">
                  <i class="ti ti-trash me-2"></i> {{ __('Delete') }}
                </a>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8">
            <div class="my-5 text-center">
                <h4 class="text-muted">{{ __('No data found') }}</h4>
                <p class="mb-4">
                  {{ __('No data found, please sprinkle some data in my virtual bowl, and let the fun begin!') }}
                </p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#employeeModal">
                    {{ __('Add New Employee') }}
                </button>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="card-footer border-top px-4 py-3">
    {{ $employees->links() }}
  </div>

</div>

{{-- Modals --}}
@include('_partials/_modals/modal-employee')

<!-- Delete Employee Modal -->
<div wire:ignore.self class="modal fade" id="deleteEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('Delete Confirmation') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-4">
          <i class="ti ti-alert-triangle text-warning ti-xl mb-3"></i>
          <p class="h5">{{ __('Are you sure you want to delete this employee') }}</p>
          <p class="text-muted">{{ __('This action will delete the employee record.') }}</p>
        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button wire:click="deleteEmployee({{ $confirmedId }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Confirm Delete') }}</button>
      </div>
    </div>
  </div>
</div>
</div>
