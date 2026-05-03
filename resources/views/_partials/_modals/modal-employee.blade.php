@push('custom-css')
  <style>
    input::-webkit-outer-spin-button,
      input::-webkit-inner-spin-button {
          -webkit-appearance: none;
          margin: 0;
      }

    input[type="number"] {
        -moz-appearance: textfield;
    }
  </style>
@endpush

<div wire:ignore.self class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-simple">
    <div class="modal-content p-0 p-md-5">
      <div class="modal-body">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        <div class="text-center mb-4">
          <h3 class="mb-2">{{ $isEdit ? __('Update Employee') : __('New Employee') }}</h3>
          <p class="text-muted">{{ __('Please fill out the following information') }}</p>
        </div>
        <form wire:submit="submitEmployee" class="row g-3">
          {{-- ID and Full Name --}}
          @if($isEdit)
          <div class="col-md-4 col-12 mb-2">
            <label class="form-label">{{ __('Identifier') }}</label>
            <input value="{{ $employee->employee_id }}" class="form-control" type="text" disabled/>
          </div>
          @endif
          <div class="col-md-{{ $isEdit ? '8' : '12' }} col-12 mb-2">
            <label class="form-label">{{ __('Full Name') }}</label>
            <input wire:model='employeeInfo.fullName' class="form-control @error('employeeInfo.fullName') is-invalid @enderror" type="text" placeholder="{{ __('فهد العتيبي') }}"/>
            @error('employeeInfo.fullName') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Mobile and Gender --}}
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Mobile') }}</label>
            <input wire:model.defer="employeeInfo.mobileNumber" class="form-control @error('employeeInfo.mobileNumber') is-invalid @enderror" placeholder="{{ __('Enter phone number') }}" type="text">
            @error('employeeInfo.mobileNumber') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Gender') }}</label>
            <select wire:model.defer="employeeInfo.gender" class="form-select @error('employeeInfo.gender') is-invalid @enderror">
              <option value=""></option>
              <option value="1">{{ __('Male') }}</option>
              <option value="0">{{ __('Female') }}</option>
            </select>
            @error('employeeInfo.gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Basic Salary and Housing Allowance --}}
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Basic Salary') }}</label>
            <input wire:model.defer='employeeInfo.basicSalary' class="form-control @error('employeeInfo.basicSalary') is-invalid @enderror" type="number" step="0.01" placeholder="5000"/>
            @error('employeeInfo.basicSalary') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Housing Allowance') }}</label>
            <input wire:model.defer='employeeInfo.housingAllowance' class="form-control @error('employeeInfo.housingAllowance') is-invalid @enderror" type="number" step="0.01" placeholder="1500"/>
            @error('employeeInfo.housingAllowance') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Transport Allowance and Other Allowances --}}
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Transport Allowance') }}</label>
            <input wire:model.defer='employeeInfo.transportAllowance' class="form-control @error('employeeInfo.transportAllowance') is-invalid @enderror" type="number" step="0.01" placeholder="500"/>
            @error('employeeInfo.transportAllowance') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Other Allowances') }}</label>
            <input wire:model.defer='employeeInfo.otherAllowances' class="form-control @error('employeeInfo.otherAllowances') is-invalid @enderror" type="number" step="0.01" placeholder="0"/>
            @error('employeeInfo.otherAllowances') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- Join Date and Annual Leave Days --}}
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Join Date') }}</label>
            <input wire:model.defer='employeeInfo.joinDate' class="form-control @error('employeeInfo.joinDate') is-invalid @enderror" type="date"/>
            @error('employeeInfo.joinDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-6 col-12 mb-2">
            <label class="form-label">{{ __('Annual Leave Days') }}</label>
            <input wire:model.defer='employeeInfo.annualLeaveDays' class="form-control @error('employeeInfo.annualLeaveDays') is-invalid @enderror" type="number"/>
            @error('employeeInfo.annualLeaveDays') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          {{-- User Account Fields --}}
          <div class="col-md-{{ $isEdit ? '12' : '4' }} col-12 mb-2">
            <label class="form-label">{{ __('Email') }}</label>
            <input wire:model.defer='employeeInfo.email' class="form-control @error('employeeInfo.email') is-invalid @enderror" type="email" placeholder="example@mail.com"/>
            @error('employeeInfo.email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          @if(!$isEdit)
          <div class="col-md-4 col-12 mb-2">
            <label class="form-label">{{ __('Password') }}</label>
            <input wire:model.defer='employeeInfo.password' class="form-control @error('employeeInfo.password') is-invalid @enderror" type="password" placeholder="············"/>
            @error('employeeInfo.password') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-md-4 col-12 mb-2">
            <label class="form-label">{{ __('Confirm Password') }}</label>
            <input wire:model.defer='employeeInfo.password_confirmation' class="form-control" type="password" placeholder="············"/>
          </div>
          @endif

          {{-- Address --}}
          <div class="col-12 mb-4">
            <label class="form-label">{{ __('Address') }} ({{ __('Optional') }})</label>
            <input wire:model.defer='employeeInfo.address' class="form-control @error('employeeInfo.address') is-invalid @enderror" type="text" />
            @error('employeeInfo.address') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>

          <div class="col-12 text-center mt-4">
            <button type="submit" class="btn btn-primary me-sm-3 me-1">{{ __('Submit') }}</button>
            <button type="reset" class="btn btn-label-secondary btn-reset" data-bs-dismiss="modal" aria-label="Close">{{ __('Cancel') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
