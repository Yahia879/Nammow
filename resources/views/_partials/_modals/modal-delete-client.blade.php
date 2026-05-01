<div wire:ignore.self class="modal fade" id="deleteClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Delete Client') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <i class="ti ti-alert-triangle text-warning ti-xl mb-3"></i>
                    <p class="h5">{{ __('Are you sure you want to delete this client?') }}</p>
                    <p class="text-muted">{{ __('This action will deactivate the client (soft delete).') }}</p>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button wire:click="deleteClient({{ $confirmedId }})" type="button" class="btn btn-danger" data-bs-dismiss="modal">{{ __('Confirm Delete') }}</button>
            </div>
        </div>
    </div>
</div>
