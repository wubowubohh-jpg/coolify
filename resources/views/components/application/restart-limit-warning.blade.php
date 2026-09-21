@props(['application'])

@if (method_exists($application, 'stoppedAfterRestartLimit') && $application->stoppedAfterRestartLimit())
    @php($restartLimit = method_exists($application, 'restartLimitMaximum') ? $application->restartLimitMaximum() : ($application->max_restart_count ?? 0))
    @php($displayRestartCount = max($application->restart_count ?? 0, $restartLimit))
    <x-status-badge
        :status="__('common.restart_limit_reached')"
        type="warning"
        :title="__('common.container_crashed_restart_limit', ['count' => $displayRestartCount])" />
@endif
