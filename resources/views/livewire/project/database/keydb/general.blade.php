<div class="application-settings-form">
    <form wire:submit="submit" class="flex flex-col gap-6">
        <x-unsaved-bar action="submit" />

        <x-application.settings-section :title="__('common.database_details')"
            :description="__('common.database_identity_description', ['type' => 'KeyDB'])">
            <x-slot:actions>
                <x-modal-input :title="__('common.resource_details_title')" :buttonTitle="__('common.details')">
                    <livewire:project.shared.resource-details :resource="$database" />
                </x-modal-input>
            </x-slot:actions>
            <div class="grid gap-4 lg:grid-cols-2">
                <x-forms.input :label="__('common.name')" id="name" canGate="update" :canResource="$database" />
                <x-forms.input :label="__('common.description')" id="description" canGate="update" :canResource="$database" />
                <div class="lg:col-span-2">
                    <x-forms.input :label="__('common.image')" id="image" required canGate="update" :canResource="$database"
                        :helper="__('common.keydb_image_helper')" />
                </div>
            </div>
        </x-application.settings-section>

        <x-application.settings-section :title="__('common.credentials')"
            :description="__('common.credentials_description', ['type' => 'KeyDB'])">
            @if ($database->started_at)
                @if ($isPasswordHiddenForMember)
                    <x-forms.input :label="__('common.password')" disabled :value="__('common.hidden_admins_only')" />
                @else
                    <x-forms.input :label="__('common.password')" id="keydbPassword" type="password" required readonly
                        :helper="__('common.database_value_change_restriction')" canGate="update" :canResource="$database" />
                @endif
            @else
                <x-callout type="warning" :title="__('common.verify_initial_credentials')">
                    {{ __('common.password_before_first_start') }}
                </x-callout>
                <div class="mt-4">
                    @if ($isPasswordHiddenForMember)
                        <x-forms.input :label="__('common.password')" disabled :value="__('common.hidden_admins_only')" />
                    @else
                        <x-forms.input :label="__('common.password')" id="keydbPassword" type="password" required
                            canGate="update" :canResource="$database" />
                    @endif
                </div>
            @endif
        </x-application.settings-section>

        <x-application.settings-section :title="__('common.runtime_and_network')"
            :description="__('common.runtime_network_description')">
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <x-forms.input
                        :helper="__('common.docker_run_options_helper')"
                        placeholder="--cap-add SYS_ADMIN --device=/dev/fuse"
                        id="customDockerRunOptions" :label="__('common.custom_docker_options')" canGate="update"
                        :canResource="$database" />
                </div>
                <x-forms.input placeholder="3000:6379" id="portsMappings" :label="__('common.port_mappings')"
                    :helper="__('common.port_mappings_helper', ['mapping' => '3000:6379'])"
                    canGate="update" :canResource="$database" />
            </div>
            <div class="mt-4">
                <livewire:project.database.keydb.status-info :database="$database" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section :title="__('common.public_access')" class="relative"
            :description="__('common.database_public_access_description')">
            <x-slot:actions>
                @if ($isPublic)
                    <x-process-dialog closeWithX size="xl">
                        <x-slot:title>{{ __('common.proxy_logs') }}</x-slot:title>
                        <x-slot:content>
                            <livewire:project.shared.get-logs :server="$server" :resource="$database"
                                container="{{ data_get($database, 'uuid') }}-proxy" :collapsible="false" lazy />
                        </x-slot:content>
                        <x-forms.button @click="processDialogOpen = true">{{ __('common.view_logs') }}</x-forms.button>
                    </x-process-dialog>
                @endif
            </x-slot:actions>
            <x-table.loading target="instantSave" :text="__('common.updating_public_access')" />
            <div class="grid gap-4 lg:grid-cols-2">
                <div wire:key="public-access-{{ $publicPort ?: 'unset' }}">
                    <x-forms.listbox id="isPublic" :label="__('common.access')" live onChange="instantSave"
                        :disabled="! auth()->user()->can('update', $database)" canGate="update" :canResource="$database" :options="[
                            ['value' => false, 'label' => __('common.private')],
                            ['value' => true, 'label' => blank($publicPort) ? __('common.public_tcp_proxy_set_port') : __('common.public_tcp_proxy'), 'disabled' => blank($publicPort)],
                        ]" />
                </div>
                <x-forms.input type="number" placeholder="6379" disabled="{{ $isPublic }}" id="publicPort"
                    :label="__('common.public_port')" canGate="update" :canResource="$database" />
                <x-forms.input type="number" placeholder="3600" disabled="{{ $isPublic }}" id="publicPortTimeout"
                    :label="__('common.proxy_timeout')" :helper="__('common.proxy_timeout_helper')"
                    canGate="update" :canResource="$database" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section :title="__('common.configuration')"
            :description="__('common.keydb_configuration_description')">
            <x-forms.textarea
                :helper="__('common.keydb_configuration_helper')"
                :label="__('common.custom_database_configuration', ['type' => 'KeyDB'])" rows="10" id="keydbConf" canGate="update"
                :canResource="$database" />
        </x-application.settings-section>

        <x-application.settings-section :title="__('common.log_delivery')"
            :description="__('common.log_delivery_description')">
            <x-forms.listbox canGate="update" :canResource="$database" id="isLogDrainEnabled" :label="__('common.log_drain')" live onChange="instantSaveAdvanced"
                :disabled="! auth()->user()->can('update', $database)" :options="[
                    ['value' => false, 'label' => __('common.do_not_forward_logs')],
                    ['value' => true, 'label' => __('common.forward_logs_server_drain')],
                ]" />
        </x-application.settings-section>
    </form>
</div>
