<div>
    <x-slot:title>
        {{ __('common.sentinel_logs') }} | Coolify
    </x-slot>
    <livewire:server.navbar :server="$server" />
    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="sentinel" />
        <div class="application-settings-form w-full">
            <x-application.settings-section :title="__('common.sentinel_logs')"
                :helper="__('common.sentinel_logs_description')"
                flush class="logs-settings-section">
                @if ($server->isSentinelEnabled())
                    <x-slot:actions>
                        <x-status-badge :status="$server->isSentinelLive() ? __('common.in_sync') : __('common.out_of_sync')"
                            :type="$server->isSentinelLive() ? 'success' : 'warning'"
                            class="logs-section-status-badge" />
                    </x-slot:actions>
                    <div class="settings-log-panel">
                        <livewire:project.shared.get-logs :server="$server" container="coolify-sentinel"
                            :displayName="__('common.sentinel')" :collapsible="false" />
                    </div>
                @else
                    <x-empty size="sm" :title="__('common.sentinel_unavailable')"
                        :description="__('common.sentinel_not_on_build_swarm')"
                        icon-name="dashboard" />
                @endif
            </x-application.settings-section>
        </div>
    </div>
</div>
