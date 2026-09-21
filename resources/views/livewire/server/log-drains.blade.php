<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > {{ __('common.log_drains') }} | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="log-drains" />

        <div class="application-settings-form flex w-full flex-col gap-6">
            @if ($server->isFunctional())
                <x-application.settings-section id="server-log-drains-overview-section" :title="__('common.log_drains')"
                    :helper="__('common.log_drains_description')">
                    <x-slot:actions>
                        <x-status-badge :status="$server->isLogDrainEnabled() ? __('common.active') : __('common.not_configured')"
                            :type="$server->isLogDrainEnabled() ? 'success' : 'neutral'" />
                    </x-slot:actions>
                    <p class="text-sm leading-6 text-neutral-600 dark:text-fg-dim">
                        {{ __('common.log_drains_only_one') }}
                    </p>
                </x-application.settings-section>

                <form wire:submit="submit" class="contents">
                    <x-unsaved-bar action="submit" />

                    <x-application.settings-section id="server-new-relic-drain-section" title="New Relic"
                        :helper="__('common.new_relic_log_api_helper')">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <x-forms.listbox canGate="update" :canResource="$server" id="isLogDrainNewRelicEnabled" :label="__('common.status')"
                                onChange="instantSave" :options="[
                                    ['value' => false, 'label' => 'Disabled'],
                                    ['value' => true, 'label' => 'Enabled'],
                                ]"
                                :disabled="$isLogDrainAxiomEnabled || $isLogDrainCustomEnabled || !auth()->user()->can('update', $server)" />
                            <x-forms.input canGate="update" :canResource="$server" type="password" required
                                id="logDrainNewRelicLicenseKey" label="License key"
                                :disabled="$server->isLogDrainEnabled()" />
                            <x-forms.input canGate="update" :canResource="$server" required
                                id="logDrainNewRelicBaseUri" label="Endpoint"
                                placeholder="https://log-api.eu.newrelic.com/log/v1"
                                helper="Use the EU or US New Relic Log API endpoint."
                                :disabled="$server->isLogDrainEnabled()" />
                        </div>
                    </x-application.settings-section>
                    <x-application.settings-section id="server-axiom-drain-section" title="Axiom"
                        :helper="__('common.axiom_ingest_api_helper')">
                        <div class="grid gap-4 lg:grid-cols-3">
                            <x-forms.listbox canGate="update" :canResource="$server" id="isLogDrainAxiomEnabled" :label="__('common.status')"
                                onChange="instantSave" :options="[
                                    ['value' => false, 'label' => 'Disabled'],
                                    ['value' => true, 'label' => 'Enabled'],
                                ]"
                                :disabled="$isLogDrainNewRelicEnabled || $isLogDrainCustomEnabled || !auth()->user()->can('update', $server)" />
                            <x-forms.input canGate="update" :canResource="$server" type="password" required
                                id="logDrainAxiomApiKey" label="API key"
                                :disabled="$server->isLogDrainEnabled()" />
                            <x-forms.input canGate="update" :canResource="$server" required
                                id="logDrainAxiomDatasetName" label="Dataset name"
                                :disabled="$server->isLogDrainEnabled()" />
                        </div>
                    </x-application.settings-section>
                    <x-application.settings-section id="server-custom-drain-section" :title="__('common.custom_fluent_bit')"
                        :helper="__('common.custom_fluent_bit_helper')">
                        <div class="mb-4 max-w-sm">
                            <x-forms.listbox canGate="update" :canResource="$server" id="isLogDrainCustomEnabled" :label="__('common.status')"
                                onChange="instantSave" :options="[
                                    ['value' => false, 'label' => 'Disabled'],
                                    ['value' => true, 'label' => 'Enabled'],
                                ]"
                                :disabled="$isLogDrainNewRelicEnabled || $isLogDrainAxiomEnabled || !auth()->user()->can('update', $server)" />
                        </div>
                        <div class="grid gap-4 lg:grid-cols-2">
                            <x-forms.textarea canGate="update" :canResource="$server" rows="8" required
                                id="logDrainCustomConfig" :label="__('common.fluent_bit_configuration')"
                                :disabled="$server->isLogDrainEnabled()" />
                            <x-forms.textarea canGate="update" :canResource="$server" rows="8"
                                id="logDrainCustomConfigParser" :label="__('common.parser_configuration')"
                                :disabled="$server->isLogDrainEnabled()" />
                        </div>
                    </x-application.settings-section>
                </form>
            @else
                <x-application.settings-section :title="__('common.log_drains')"
                    :helper="__('common.log_drains_description')">
                    <x-empty size="sm" :title="__('common.server_validation_required')"
                        :description="__('common.validate_server_before_log_drains')"
                        icon-name="notifications" />
                </x-application.settings-section>
            @endif
        </div>
    </div>
</div>
