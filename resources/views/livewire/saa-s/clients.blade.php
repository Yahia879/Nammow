<div>
    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">SaaS /</span> {{ __('Clients') }}
    </h4>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">{{ __('Clients List') }}</h5>
            <div class="d-flex align-items-center">
                <input wire:model.live="searchTerm" type="text" class="form-control" placeholder="{{ __('Search Clients...') }}">
            </div>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Client') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Plan') }}</th>
                        <th>{{ __('Companies') }}</th>
                        <th>{{ __('Joined') }}</th>
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
                                {{ ucfirst($client->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-body">{{ $client->plan_id ? __('Plan ') . $client->plan_id : __('No Plan') }}</span>
                        </td>
                        <td>
                            <span class="badge rounded-pill bg-label-info">{{ $client->companies_count }}</span>
                        </td>
                        <td>
                            <span class="text-body">{{ $client->created_at->format('Y-m-d') }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="mt-4 mb-4">
                                <h5 class="mb-1">{{ __('No Clients Found') }}</h5>
                                <p class="text-muted">{{ __('Try adjusting your search or add a new client.') }}</p>
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
</div>
