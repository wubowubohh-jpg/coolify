@props([
    'title' => null,
    'lastDeploymentLink' => null,
    'resource' => null,
    'showRefreshButton' => true,
])
@php
    $stoppedAfterRestartLimit = $resource && method_exists($resource, 'stoppedAfterRestartLimit') && $resource->stoppedAfterRestartLimit();
@endphp
<div class="flex flex-wrap items-center gap-1">
    @if (str($resource->status)->startsWith('running'))
        <x-status.running :status="$resource->status" :title="$title" :lastDeploymentLink="$lastDeploymentLink" />
    @elseif(str($resource->status)->startsWith('degraded'))
        <x-status.degraded :status="$resource->status" :title="$title" :lastDeploymentLink="$lastDeploymentLink" />
    @elseif(str($resource->status)->startsWith('restarting') || str($resource->status)->startsWith('starting'))
        <x-status.restarting :status="$resource->status" :title="$title" :lastDeploymentLink="$lastDeploymentLink" />
    @else
        <x-status.stopped :status="$resource->status" />
    @endif
    @if (isset($resource->restart_count) && $resource->restart_count > 0 && (!str($resource->status)->startsWith('exited') || $stoppedAfterRestartLimit))
        <x-status-badge :status="__('common.restarts', ['count' => $resource->restart_count])" type="warning"
            :title="__('common.container_restarted', ['count' => $resource->restart_count]).' '.__('common.last_restart', ['time' => $resource->last_restart_at?->diffForHumans()])" />
    @endif
    @if ($stoppedAfterRestartLimit)
        <x-application.restart-limit-warning :application="$resource" />
    @endif
    @if (!str($resource->status)->contains('exited') && $showRefreshButton)
        <x-status-badge as="button" wire:target="manualCheckStatus" wire:loading.attr="disabled"
            wire:click='manualCheckStatus' :status="__('common.refresh')" type="neutral" :title="__('common.refresh_status')"
            aria-label="{{ __('common.refresh_status') }}"
            class="min-w-[4.5rem] justify-center cursor-pointer border-transparent hover:bg-neutral-200 disabled:cursor-wait disabled:opacity-70 dark:hover:bg-coolgray-300" />
    @endif
</div>
