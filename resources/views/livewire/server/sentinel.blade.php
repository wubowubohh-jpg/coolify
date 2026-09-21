@php
    $sentinelStatusLabel = match ($sentinelStatus) {
        'restarting' => __('common.restarting_label'),
        'waiting' => __('common.waiting_first_report'),
        'in_sync' => __('common.in_sync'),
        default => __('common.out_of_sync'),
    };
    $sentinelStatusType = match ($sentinelStatus) {
        'in_sync' => 'success',
        'out_of_sync' => 'warning',
        default => 'neutral',
    };
@endphp

<div class="application-settings-form flex w-full flex-col gap-6" wire:poll.10s="refreshSentinelStatus">
    <form wire:submit.prevent="submit" class="contents">
        {{-- Scope dirty tracking to savable form fields only. Without wire:target,
             Livewire compares the entire component snapshot — so dev-only x-init
             `$wire.set('sentinelCustomDockerImage', …)` (and similar) briefly
             flashes this bar on every page open. --}}
        <x-unsaved-bar action="submit"
            targets="sentinelCustomUrl,sentinelToken,sentinelMetricsRefreshRateSeconds,sentinelMetricsHistoryDays,sentinelPushIntervalSeconds" />

        <x-application.settings-section id="server-sentinel-overview-section" :title="__('common.sentinel')"
            :helper="__('common.sentinel_overview_helper')">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <x-status-badge :status="$sentinelStatusLabel" :type="$sentinelStatusType" />
                    <x-forms.button wire:click="restartSentinel" canGate="update"
                        :canResource="$server">
                        <x-reicon name="refresh" class="size-3.5" />
                        {{ $sentinelStatus === 'in_sync' ? __('common.restart') : __('common.sync') }}
                    </x-forms.button>
                </div>
            </x-slot:actions>

            @if ($sentinelStatus === 'out_of_sync')
                <x-callout type="warning" :title="__('common.sentinel_out_of_sync')">
                    <div class="space-y-3">
                        <p>{{ __('common.sentinel_not_reported') }}</p>
                        <ul class="list-disc space-y-1 pl-4">
                            <li>{!! __('common.confirm_sentinel_container') !!}</li>
                            <li>
                                <a class="font-medium underline underline-offset-2"
                                    href="{{ route('server.sentinel.logs', ['server_uuid' => $server->uuid]) }}"
                                    wire:navigate>{{ __('common.open_sentinel_logs') }}</a>
                                {{ __('common.review_sentinel_errors') }}
                            </li>
                            <li>{{ __('common.confirm_sentinel_configuration') }}</li>
                        </ul>

                        @if ($server->isLocalhost())
                            <p>{{ __('common.sync_sentinel_localhost') }}</p>
                        @else
                            <div class="space-y-2">
                                <p>{{ __('common.remote_sentinel_outbound_access') }}</p>
                                @if (filled($sentinelCustomUrl))
                                    <p>
                                        {!! __('common.test_remote_sentinel', ['command' => '<code class="break-all">curl -fsS '.e(escapeshellarg(rtrim($sentinelCustomUrl, '/').'/api/health')).'</code>']) !!}
                                    </p>
                                @else
                                    <p>{{ __('common.set_reachable_coolify_url') }}</p>
                                @endif
                                <p>{{ __('common.check_sentinel_network') }}</p>
                            </div>
                        @endif
                    </div>
                </x-callout>
            @elseif ($sentinelStatus === 'in_sync')
                <p class="text-sm text-neutral-500 dark:text-fg-dim">
                    {{ __('common.sentinel_connected') }}
                </p>
            @elseif ($sentinelStatus === 'restarting')
                <p class="text-sm text-neutral-500 dark:text-fg-dim">{{ __('common.sentinel_restarting') }}</p>
            @else
                <p class="text-sm text-neutral-500 dark:text-fg-dim">{{ __('common.sentinel_waiting_report') }}</p>
            @endif
        </x-application.settings-section>

        @if ($server->isSentinelEnabled())
            <x-application.settings-section id="server-sentinel-connection-section" :title="__('common.connection')"
                :helper="__('common.sentinel_connection_helper')">
                <x-slot:actions>
                    <div class="flex items-center gap-2">
                        @can('manageSentinel', $server)
                            <x-modal-confirmation :title="__('common.restore_default_sentinel_configuration')"
                                :buttonTitle="__('common.restore_defaults')" submitAction="restoreDefaultConfiguration"
                                :actions="[
                                    __('common.restore_generated_sentinel_settings'),
                                    __('common.clear_sentinel_debug_override'),
                                    __('common.sentinel_token_metrics_preserved'),
                                    __('common.restart_sentinel_apply'),
                                ]" :warningMessage="__('common.custom_sentinel_replaced')"
                                :confirmWithText="false" :confirmWithPassword="false"
                                :step2ButtonText="__('common.restore_defaults')" />
                        @endcan
                        <x-forms.button canGate="update" :canResource="$server"
                            wire:click="regenerateSentinelToken">
                            {{ __('common.regenerate_token') }}
                        </x-forms.button>
                    </div>
                </x-slot:actions>
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.input canGate="update" :canResource="$server" id="sentinelCustomUrl"
                        required :label="__('common.coolify_url')"
                        :helper="__('common.sentinel_url_helper')" />
                    <x-forms.input canGate="update" :canResource="$server" type="password"
                        id="sentinelToken" :label="__('common.sentinel_token')" required
                        :helper="__('common.sentinel_token_helper')" />
                </div>
            </x-application.settings-section>

            <x-application.settings-section id="server-sentinel-metrics-section" :title="__('common.metrics_collection')"
                :helper="__('common.metrics_collection_helper')">
                <div class="grid gap-4 lg:grid-cols-3">
                    <x-forms.input canGate="update" :canResource="$server" type="number" min="1"
                        id="sentinelMetricsRefreshRateSeconds" :label="__('common.collection_rate')" required
                        :helper="__('common.metric_sample_seconds')" />
                    <x-forms.input canGate="update" :canResource="$server" type="number" min="1"
                        id="sentinelMetricsHistoryDays" :label="__('common.history_retention')" required
                        :helper="__('common.history_retention_helper')" />
                    <x-forms.input canGate="update" :canResource="$server" type="number" min="10"
                        id="sentinelPushIntervalSeconds" :label="__('common.push_interval')" required
                        :helper="__('common.push_interval_helper')" />
                </div>
            </x-application.settings-section>

            @if (isDev())
                <x-application.settings-section id="server-sentinel-development-section"
                    :title="__('common.development_overrides')"
                    :helper="__('common.development_overrides_helper')">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-forms.listbox id="isSentinelDebugEnabled" :label="__('common.debug_logging')"
                            onChange="instantSave" :options="[
                                ['value' => false, 'label' => __('common.standard_logging')],
                                ['value' => true, 'label' => __('common.enable_debug_logging')],
                            ]" />
                        <div x-data="{
                            customImage: localStorage.getItem('sentinel_custom_docker_image_{{ $server->uuid }}') || '',
                            saveCustomImage() {
                                localStorage.setItem('sentinel_custom_docker_image_{{ $server->uuid }}', this.customImage);
                                $wire.set('sentinelCustomDockerImage', this.customImage || null);
                            }
                        }"
                            @sentinel-defaults-restored.window="localStorage.removeItem('sentinel_custom_docker_image_{{ $server->uuid }}'); customImage = ''"
                            {{-- Only hydrate Livewire when a real override exists. Unconditional
                                 $wire.set('', null→'') on every open marks the component dirty and
                                 flashes the unsaved bar until the round-trip completes. --}}
                            x-init="if (customImage) { $wire.set('sentinelCustomDockerImage', customImage) }">
                            <x-forms.input canGate="update" :canResource="$server" x-model="customImage"
                                @input.debounce.500ms="saveCustomImage()"
                                placeholder="sentinel:latest" :label="__('common.custom_docker_image')"
                                :helper="__('common.default_sentinel_image_helper')" />
                        </div>
                    </div>
                </x-application.settings-section>
            @endif
        @endif
    </form>
</div>
