<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > {{ __('common.cloudflare_tunnel') }} | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="cloudflare-tunnel" />

        <div class="application-settings-form flex w-full flex-col gap-6">
            <x-application.settings-section id="server-cloudflare-overview-section" :title="__('common.cloudflare_tunnel')"
                :helper="__('common.proxy_ssh_description')">
                <x-slot:actions>
                    <x-status-badge :status="$isCloudflareTunnelsEnabled ? __('common.enabled') : __('common.disabled')"
                        :type="$isCloudflareTunnelsEnabled ? 'success' : 'neutral'" />
                </x-slot:actions>

                @if ($isCloudflareTunnelsEnabled)
                    <x-callout type="warning" :title="__('common.disabling_tunnel_warning')">
                        {{ __('common.restore_direct_ip') }}
                    </x-callout>
                    <div class="mt-4">
                        <x-modal-confirmation :title="__('common.disable_cloudflare_tunnel')"
                            :buttonTitle="__('common.disable_cloudflare_tunnel_button')" isErrorButton
                            submitAction="toggleCloudflareTunnels" :actions="$server->ip_previous
                                ? [
                                    __('common.tunnel_disabled_server'),
                                    __('common.server_ip_restored'),
                                ]
                                : [
                                    __('common.tunnel_disabled_server'),
                                    __('common.restore_direct_ip_manually'),
                                    __('common.server_inaccessible_until_corrected'),
                                ]"
                            confirmationText="DISABLE CLOUDFLARE TUNNEL"
                            :confirmationLabel="__('common.type_confirmation_disable_tunnel')"
                            :shortConfirmationLabel="__('common.confirmation_text')" />
                    </div>
                @elseif (!$server->isFunctional())
                    <x-callout type="info" :title="__('common.validate_for_automated_setup')">
                        {{ __('common.automated_setup_requirements') }}
                        You can also
                        <button type="button" wire:click="manualCloudflareConfig" class="font-medium underline">
                            {{ __('common.manual_configuration_complete') }}
                        </button>.
                    </x-callout>
                @else
                    <p class="text-sm leading-6 text-neutral-600 dark:text-fg-dim">
                        {{ __('common.choose_tunnel_setup') }}
                    </p>
                @endif
            </x-application.settings-section>

            @if (!$isCloudflareTunnelsEnabled && $server->isFunctional())
                <x-application.settings-section id="server-cloudflare-automated-section" :title="__('common.automated_setup')"
                    :helper="__('common.automated_setup_description')">
                    <x-slot:actions>
                        <a class="button"
                            href="https://coolify.io/docs/knowledge-base/cloudflare/tunnels/server-ssh"
                            target="_blank">
                            {{ __('common.documentation') }}
                            <x-external-link />
                        </a>
                    </x-slot:actions>

                    @cannot('update', $server)
                        <x-callout type="danger" :title="__('common.insufficient_permissions')">
                            {{ __('common.no_permission_configure_tunnel') }}
                        </x-callout>
                    @else
                        <x-process-dialog @automated.window="processDialogOpen = true" closeWithX size="xl">
                            <x-slot:title>{{ __('common.cloudflare_tunnel_configuration') }}</x-slot:title>
                            <x-slot:content>
                                <livewire:activity-monitor header="Logs" fullHeight />
                            </x-slot:content>
                        </x-process-dialog>
                        <form @submit.prevent="$wire.dispatch('automatedCloudflareConfig')">
                            <div class="grid gap-4 lg:grid-cols-2">
                                <x-forms.input id="cloudflare_token" required :label="__('common.cloudflare_token')"
                                    type="password" />
                                <x-forms.input id="ssh_domain" :label="__('common.ssh_domain')" required
                                    :helper="__('common.ssh_domain_helper')" />
                            </div>
                            <div class="mt-4 flex justify-end">
                                <x-forms.button type="submit" isHighlighted>{{ __('common.configure_tunnel') }}</x-forms.button>
                            </div>
                        </form>
                    @endcannot
                </x-application.settings-section>

                <x-application.settings-section id="server-cloudflare-manual-section" :title="__('common.manual_setup')"
                    :helper="__('common.manual_setup_description')">
                    @can('update', $server)
                        <x-modal-confirmation :title="__('common.confirm_manual_tunnel')"
                            :buttonTitle="__('common.manual_tunnel_configured_button')"
                            submitAction="manualCloudflareConfig" :actions="[
                                __('common.cloudflare_configured'),
                                __('common.incomplete_setup_unreachable'),
                            ]" confirmationText="I manually configured Cloudflare Tunnel"
                            :confirmationLabel="__('common.type_confirmation_continue')"
                            :shortConfirmationLabel="__('common.confirmation_text')" />
                    @endcan
                </x-application.settings-section>

                @script
                    <script>
                        $wire.$on('automatedCloudflareConfig', () => {
                            window.dispatchEvent(new CustomEvent('automated'));
                            $wire.$call('automatedCloudflareConfig');
                        });
                    </script>
                @endscript
            @endif
        </div>
    </div>
</div>
