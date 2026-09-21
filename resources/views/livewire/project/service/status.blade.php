<div wire:poll.10000ms="refreshStatus" class="flex items-center gap-1">
    @php($displayStatus = $selectedResource?->status ?? $service->status)
    <x-status-summary :status="$displayStatus" :title="$selectedResource ? __('common.resource_status') : __('common.service_status')"
        :container-name="$selectedResource ? 'Container' : 'Containers'" />
    @if ($selectedResource)
        <x-application.restart-limit-warning :application="$selectedResource" />
    @endif
</div>
