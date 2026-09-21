<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > Delete Server | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="danger" />

        <div class="application-settings-form w-full">
            @if (! $server->is_coolify_host)
                <x-application.settings-section id="server-danger-section" :title="__('common.delete_server')"
                    :helper="__('common.delete_server_description')"
                    class="server-danger-section">
                    <x-danger-zone :title="__('common.action_cannot_be_undone')">
                        <p>
                        {{ __('common.server_removed_from_coolify') }}
                        @if ($server->definedResources()->count() > 0)
                            {{ __('common.server_contains_resources') }}
                        @endif
                        </p>
                        <p>{{ __('common.type_server_name_to_continue') }}</p>
                        <x-slot:action>
                        <x-modal-confirmation :title="__('common.confirm_server_deletion')" isErrorButton
                            :buttonTitle="__('common.delete_server')" submitAction="delete"
                            :actions="[__('common.server_delete_confirmation_action')]"
                            :checkboxes="$checkboxes" confirmationText="{{ $server->name }}"
                            :confirmationLabel="__('common.enter_server_name')"
                            :shortConfirmationLabel="__('common.server_name')" />
                        </x-slot:action>
                    </x-danger-zone>
                </x-application.settings-section>
            @endif
        </div>
    </div>
</div>
