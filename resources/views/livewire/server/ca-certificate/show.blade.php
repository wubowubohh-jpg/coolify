<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > CA Certificate | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="ca-certificate" />

        <div class="application-settings-form flex w-full flex-col gap-6">
            <x-application.settings-section id="server-ca-overview-section" :title="__('common.ca_certificate')"
                :helper="__('common.certificate_authority_description')">
                <x-slot:actions>
                    @if ($certificateValidUntil)
                        <x-status-badge
                            :status="now()->gt($certificateValidUntil)
                                ? 'Expired'
                                : (now()->addDays(30)->gt($certificateValidUntil) ? 'Expiring soon' : 'Valid')"
                            :type="now()->gt($certificateValidUntil) || now()->addDays(30)->gt($certificateValidUntil)
                                ? 'error'
                                : 'success'" />
                    @endif
                </x-slot:actions>

                <x-callout type="info" :title="__('common.using_certificate')">
                    {{ __('common.read_only_certificate_mount') }}
                    <a class="font-medium underline" href="https://coolify.io/docs/databases/ssl" target="_blank">
                        {{ __('common.read_ssl_guide') }}
                    </a>
                </x-callout>

                <div class="mt-4">
                    <p class="mb-1.5 text-xs font-medium text-neutral-500 dark:text-fg-dim">{{ __('common.read_only_bind_mount') }}</p>
                    <x-forms.copy-button
                        text="- /data/coolify/ssl/coolify-ca.crt:/etc/ssl/certs/coolify-ca.crt:ro" />
                </div>
            </x-application.settings-section>

            <x-application.settings-section id="server-ca-content-section" :title="__('common.certificate_content')"
                :helper="__('common.certificate_content_description')">
                <x-slot:actions>
                    <div class="flex items-center gap-2">
                        @can('view', $server)
                            <x-forms.button wire:click="toggleCertificate" type="button">
                                {{ $showCertificate ? __('common.hide_certificate') : __('common.show_certificate') }}
                            </x-forms.button>
                        @endcan
                        @can('update', $server)
                            <x-modal-confirmation :title="__('common.confirm_ca_certificate_change')"
                                :buttonTitle="__('common.save_certificate')" submitAction="saveCaCertificate" :actions="[
                                    __('common.custom_certificate_overwrites'),
                                    __('common.database_certificates_regenerated_custom'),
                                    __('common.redeploy_affected_databases'),
                                ]" confirmationText="/data/coolify/ssl/coolify-ca.crt"
                                :shortConfirmationLabel="__('common.ca_certificate_path')"
                                :step3ButtonText="__('common.save_certificate_step')" />
                            <x-modal-confirmation :title="__('common.confirm_regenerate_certificate')"
                                :buttonTitle="__('common.regenerate')" submitAction="regenerateCaCertificate" :actions="[
                                    __('common.replace_current_ca_certificate'),
                                    __('common.database_certificates_regenerated_new'),
                                    __('common.redeploy_affected_databases'),
                                ]" confirmationText="/data/coolify/ssl/coolify-ca.crt"
                                :shortConfirmationLabel="__('common.ca_certificate_path')"
                                :step3ButtonText="__('common.regenerate_certificate_step')" />
                        @endcan
                    </div>
                </x-slot:actions>

                @if ($showCertificate)
                    <x-forms.textarea canGate="update" :canResource="$server" id="certificateContent"
                        rows="15" :label="__('common.pem_certificate')"
                        :placeholder="__('common.paste_certificate_content')" />
                @else
                    <div
                        class="flex min-h-72 flex-col items-center justify-center rounded-lg bg-neutral-100/70 px-6 text-center ring-1 ring-neutral-200 dark:bg-black/20 dark:ring-white/[0.08]">
                        <x-reicon name="keys" class="size-8 text-neutral-300 dark:text-fg-faint" />
                        <p class="mt-3 text-sm font-medium text-neutral-950 dark:text-fg">{{ __('common.certificate_hidden') }}</p>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-fg-dim">
                            {{ __('common.show_certificate_description') }}
                        </p>
                    </div>
                @endif
            </x-application.settings-section>
        </div>
    </div>
</div>
