<div>
    <x-slot:title>
        {{ __('common.sentinel_configuration') }} | Coolify
    </x-slot>
    <livewire:server.navbar :server="$server" />
    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="sentinel" />
        @if ($server->isFunctional())
            <div class="w-full">
                <livewire:server.sentinel :server="$server" />
            </div>
        @else
            <div class="application-settings-form w-full">
                <x-application.settings-section :title="__('common.sentinel')"
                    :helper="__('common.sentinel_helper')">
                    <x-empty size="sm" :title="__('common.server_validation_required')"
                        :description="__('common.validate_server_before_sentinel')"
                        icon-name="dashboard" />
                </x-application.settings-section>
            </div>
        @endif
    </div>
</div>
