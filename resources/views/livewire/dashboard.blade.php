<div>

  @php
  $configData = Helper::appClasses();
  use App\Models\Employee;
  use Carbon\Carbon;
  @endphp

  @section('title', 'Dashboard')

  @section('vendor-style')

  @endsection

  @section('page-style')
  <style>
    .match-height>[class*='col'] {
      display: flex;
      flex-flow: column;
    }

    .match-height>[class*='col']>.card {
      flex: 1 1 auto;
    }

    .btn-tr {
      opacity: 0;
    }

    tr:hover .btn-tr {
      display: inline-block;
      opacity: 1;
    }

    tr:hover .td {
      color: #7367f0 !important;
    }
  </style>
  @endsection

  {{-- Alerts --}}
  @include('_partials/_alerts/alert-general')

  @if(Auth::user()->hasRole('company'))
    <div>
        <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">{{ __('Company') }} /</span> {{ __('Dashboard') }}
        </h4>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        {{ __('Welcome to the Company Dashboard. Manage your employees here.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
  @else
    {{-- ORIGINAL DASHBOARD CONTENT (FROZEN) --}}
    {{--
    <div class="alert alert-danger alert-dismissible" style="text-align: justify;" role="alert">
      <h5 class="alert-heading mb-2">{{ __('Reminder!') }}</h5>
...
    @push('custom-scripts')
    <script>
      function updateClock() {
              const now = new Date();
              const dateOptions = {
                  weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
              };
              const timeOptions = {
                  hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false
              };

              const formattedDate = now.toLocaleDateString('en-US', dateOptions);
              const formattedTime = now.toLocaleTimeString('en-US', timeOptions);

              document.getElementById('date').innerHTML = formattedDate;
              document.getElementById('time').innerHTML = formattedTime;
          }

          setInterval(updateClock, 1000); // Update every second
          updateClock(); // Initial call to display clock immediately
    </script>
    @endpush
    --}}
  @endif
</div>
