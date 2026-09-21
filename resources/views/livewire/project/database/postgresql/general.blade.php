<div class="application-settings-form flex flex-col gap-6">
    <form wire:submit="submit" class="flex flex-col gap-6">
        <x-unsaved-bar action="submit" />

        <x-application.settings-section id="database-details-section" :title="__('common.database_details')"
            :description="__('common.database_identity_description', ['type' => 'PostgreSQL'])">
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
                        :helper="__('common.database_image_helper', ['type' => 'PostgreSQL'])" />
                </div>
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="credentials-section" :title="__('common.credentials')"
            :description="__('common.credentials_description', ['type' => 'PostgreSQL'])">
            @if ($database->started_at)
                <x-callout type="warning" :title="__('common.keep_credentials_synchronized')">
                    {{ __('common.credentials_sync_description', ['type' => 'PostgreSQL']) }}
                </x-callout>
            @endif
            <div class="{{ $database->started_at ? 'mt-4 ' : '' }}grid gap-4 lg:grid-cols-2">
                <x-forms.input :label="__('common.username')" id="postgresUser" :placeholder="__('common.if_empty_postgres')"
                    canGate="update" :canResource="$database" />
                @if ($isPasswordHiddenForMember)
                    <x-forms.input :label="__('common.password')" disabled :value="__('common.hidden_admins_only')" />
                @else
                    <x-forms.input :label="__('common.password')" id="postgresPassword" type="password" required
                        canGate="update" :canResource="$database" />
                @endif
                <x-forms.input :label="__('common.initial_database')" id="postgresDb"
                    :placeholder="__('common.initial_database_matches_username')"
                    :readonly="(bool) $database->started_at" canGate="update" :canResource="$database"
                    :helper="$database->started_at ? __('common.database_value_change_restriction') : null" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="initialization-section" :title="__('common.initialization')"
            :description="__('common.initialization_description')">
            <div class="grid gap-4 lg:grid-cols-2">
                <x-forms.input :label="__('common.initial_database_arguments')" id="postgresInitdbArgs"
                    :placeholder="__('common.leave_empty_image_default')" canGate="update" :canResource="$database" />
                <x-forms.input :label="__('common.host_authentication_method')" id="postgresHostAuthMethod"
                    :placeholder="__('common.leave_empty_image_default')" canGate="update" :canResource="$database" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="runtime-network-section" :title="__('common.runtime_and_network')"
            :description="__('common.runtime_network_description')">
            <div class="grid gap-4 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <x-forms.input
                        :helper="__('common.docker_run_options_helper')"
                        placeholder="--cap-add SYS_ADMIN --device=/dev/fuse"
                        id="customDockerRunOptions" :label="__('common.custom_docker_options')" canGate="update"
                        :canResource="$database" />
                </div>
                <x-forms.input placeholder="3000:5432" id="portsMappings" :label="__('common.port_mappings')"
                    :helper="__('common.port_mappings_helper', ['mapping' => '3000:5432'])"
                    canGate="update" :canResource="$database" />
            </div>
            <div class="mt-4">
                <livewire:project.database.postgresql.status-info :database="$database" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="public-access-section" :title="__('common.public_access')" class="relative"
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
                    <x-forms.listbox id="isPublic" :label="__('common.access')" live onChange="instantSave" :onChangeArgs="[]"
                        :disabled="! auth()->user()->can('update', $database)" canGate="update" :canResource="$database" :options="[
                            ['value' => false, 'label' => __('common.private')],
                            ['value' => true, 'label' => blank($publicPort) ? __('common.public_tcp_proxy_set_port') : __('common.public_tcp_proxy'), 'disabled' => blank($publicPort)],
                        ]" />
                </div>
                <x-forms.input type="number" placeholder="5432" disabled="{{ $isPublic }}" id="publicPort"
                    :label="__('common.public_port')" canGate="update" :canResource="$database" />
                <x-forms.input type="number" placeholder="3600" disabled="{{ $isPublic }}" id="publicPortTimeout"
                    :label="__('common.proxy_timeout')" :helper="__('common.proxy_timeout_helper')"
                    canGate="update" :canResource="$database" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="configuration-section" :title="__('common.configuration')"
            :description="__('common.database_configuration_description', ['type' => 'PostgreSQL'])">
            <x-forms.textarea :label="__('common.custom_database_configuration', ['type' => 'PostgreSQL'])" rows="10" id="postgresConf"
                canGate="update" :canResource="$database" />
        </x-application.settings-section>

        <x-application.settings-section id="log-delivery-section" :title="__('common.log_delivery')"
            :description="__('common.log_delivery_description')">
            <x-forms.listbox canGate="update" :canResource="$database" id="isLogDrainEnabled" :label="__('common.log_drain')" live onChange="instantSaveAdvanced"
                :disabled="! auth()->user()->can('update', $database)" :options="[
                    ['value' => false, 'label' => __('common.do_not_forward_logs')],
                    ['value' => true, 'label' => __('common.forward_logs_server_drain')],
                ]" />
        </x-application.settings-section>
    </form>

    <x-application.settings-section id="initialization-scripts-section" :title="__('common.initialization_scripts')"
        :description="__('common.initialization_scripts_description')" flush>
        <x-slot:actions>
            @can('update', $database)
                <x-modal-input :buttonTitle="__('common.add')" :title="__('common.new_initialization_script')">
                    <form class="flex w-full flex-col gap-4" wire:submit="save_new_init_script">
                        <x-forms.input placeholder="create_test_db.sql" id="new_filename" :label="__('common.filename')" required />
                        <x-forms.textarea rows="16" placeholder="CREATE DATABASE test;" id="new_content"
                            :label="__('common.content')" required />
                        <div class="flex justify-end border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                            <x-forms.button type="submit">{{ __('common.add_script') }}</x-forms.button>
                        </div>
                    </form>
                </x-modal-input>
            @endcan
        </x-slot:actions>
        <div class="divide-y divide-neutral-200 dark:divide-border-subtle">
            @forelse($initScripts ?? [] as $script)
                <livewire:project.database.init-script :database="$database" :script="$script"
                    :wire:key="'init-script-'.md5($script['filename'])" />
            @empty
                <x-empty :title="__('common.no_initialization_scripts')"
                    :description="__('common.add_sql_initialization_file')" />
            @endforelse
        </div>
    </x-application.settings-section>
</div>
