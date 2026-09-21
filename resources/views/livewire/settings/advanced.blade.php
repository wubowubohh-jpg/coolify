<div>
    <x-slot:title>
        {{ __('settings.advanced') }} | Coolify
    </x-slot>

    <x-settings.layout>
        <form wire:submit="submit" class="application-settings-form flex min-w-0 flex-col gap-6">
            {{-- Scope dirty tracking to fields that need an explicit Save. Instant-save
                 listboxes (API, MCP, telemetry, …) update the snapshot on the server
                 immediately; without wire:target they briefly flash this bar. --}}
            <x-unsaved-bar action="submit"
                targets="custom_dns_servers,allowed_ips,webhook_allowed_internal_hosts,webhook_allow_localhost,domain_connect_private_key,image_cdn_url" />

            <x-application.settings-section id="access-section" :title="__('settings.access')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.listbox id="is_registration_enabled" :label="__('settings.registration')"
                        :helper="__('settings.registration_helper')"
                        onChange="instantSave" :options="[
                            ['value' => true, 'label' => __('settings.anyone_can_register')],
                            ['value' => false, 'label' => __('settings.registration_disabled')],
                        ]" />
                    <x-forms.listbox id="disable_two_step_confirmation" :label="__('settings.destructive_confirmation')"
                        :helper="__('settings.destructive_confirmation_helper')"
                        onChange="instantSave" :options="[
                            ['value' => false, 'label' => __('settings.require_two_step')],
                            ['value' => true, 'label' => __('settings.skip_two_step')],
                        ]" />
                </div>
            </x-application.settings-section>

            <x-application.settings-section id="dns-section" :title="__('settings.dns_validation')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.listbox id="is_dns_validation_enabled" :label="__('settings.dns_validation')"
                        :helper="__('settings.dns_validation_helper')" onChange="instantSave" :options="[
                            ['value' => true, 'label' => __('profile.enabled')],
                            ['value' => false, 'label' => __('common.disabled')],
                        ]" />
                    <x-forms.input id="custom_dns_servers" :label="__('settings.custom_dns_servers')"
                        :helper="__('settings.custom_dns_helper')"
                        placeholder="1.1.1.1, 8.8.8.8" />
                </div>
            </x-application.settings-section>

            @if (isCloud())
                <x-application.settings-section id="domain-connect-section" :title="__('settings.domain_connect')"
                    :helper="__('settings.domain_connect_helper')">
                    <div class="grid gap-4">
                        <x-forms.input id="domain_connect_private_key" type="password" allowToPeak
                            :label="__('settings.domain_connect_private_key')"
                            :helper="__('settings.domain_connect_private_key_helper')"
                            placeholder="-----BEGIN PRIVATE KEY-----" />
                        @if (filled(data_get($settings, 'domain_connect_private_key')))
                            <div class="flex flex-wrap items-center gap-2">
                                <x-status-badge :status="__('settings.key_configured')" type="success" />
                                <x-forms.button type="button" wire:click="clearDomainConnectPrivateKey" isError>
                                    {{ __('settings.remove_key') }}
                                </x-forms.button>
                            </div>
                        @elseif (filled(config('services.domain_connect.private_key')))
                            <x-status-badge :status="__('settings.using_domain_connect_env')" type="neutral" />
                        @else
                            <x-callout type="info" :title="__('settings.not_configured')">
                                {{ __('settings.domain_connect_hidden') }}
                            </x-callout>
                        @endif
                    </div>
                </x-application.settings-section>
            @endif

            <x-application.settings-section id="api-section" :title="__('settings.api_mcp')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.listbox id="is_api_enabled" :label="__('settings.api_access')"
                        :helper="__('settings.api_access_helper')" onChange="instantSave"
                        :options="[
                            ['value' => true, 'label' => __('profile.enabled')],
                            ['value' => false, 'label' => __('common.disabled')],
                        ]" />
                    <x-forms.listbox id="is_mcp_server_enabled" :label="__('settings.mcp_server')"
                        :helper="__('settings.mcp_server_helper')" onChange="instantSave"
                        :options="[
                            ['value' => true, 'label' => __('profile.enabled')],
                            ['value' => false, 'label' => __('common.disabled')],
                        ]" />
                    <div class="lg:col-span-2">
                        <x-forms.input id="allowed_ips" :label="__('settings.allowed_api_ips')"
                            :helper="__('settings.allowed_api_ips_helper')"
                            placeholder="192.168.1.100, 10.0.0.0/8" />
                    </div>
                </div>
                @if ($is_api_enabled && (empty($allowed_ips) || in_array('0.0.0.0', array_map('trim', explode(',', $allowed_ips ?? '')))))
                    <x-callout type="warning" :title="__('settings.api_open')" class="mt-4">
                        {{ __('settings.api_open_helper') }}
                    </x-callout>
                @endif
                @if ($is_mcp_server_enabled)
                    <x-callout type="info" :title="__('settings.mcp_endpoint')" class="mt-4">
                        <code>{{ url('/mcp') }}</code>{{ __('settings.mcp_endpoint_helper') }}
                    </x-callout>
                @endif
            </x-application.settings-section>

            <x-application.settings-section id="endpoint-section" :title="__('settings.outbound_endpoints')">
                <div class="flex flex-col gap-4">
                    <x-forms.textarea id="webhook_allowed_internal_hosts" rows="4"
                        :label="__('settings.allowed_internal_targets')"
                        :helper="__('settings.allowed_internal_targets_helper')"
                        placeholder="hooks.company.local, 10.50.0.0/16" />
                    <div class="max-w-md">
                        <x-forms.listbox id="webhook_allow_localhost" :label="__('settings.localhost_targets')"
                            :helper="__('settings.localhost_targets_helper')" :options="[
                                ['value' => true, 'label' => __('settings.allowed')],
                                ['value' => false, 'label' => __('settings.blocked')],
                            ]" />
                    </div>
                </div>
            </x-application.settings-section>

            <x-application.settings-section id="interface-section" :title="__('settings.interface_telemetry')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.listbox id="is_wire_navigate_enabled" :label="__('settings.navigation')"
                        :helper="__('settings.navigation_helper')" onChange="instantSave" :options="[
                            ['value' => true, 'label' => __('settings.spa_navigation')],
                            ['value' => false, 'label' => __('settings.full_page_navigation')],
                        ]" />
                    <x-forms.listbox id="do_not_track" :label="__('settings.anonymous_telemetry')"
                        :helper="__('settings.anonymous_telemetry_helper')" onChange="instantSave" :options="[
                            ['value' => false, 'label' => __('profile.enabled')],
                            ['value' => true, 'label' => __('common.disabled')],
                        ]" />
                    <div class="flex flex-col gap-2">
                        <x-forms.listbox id="is_sponsorship_popup_enabled" :label="__('settings.sponsorship_reminders')"
                            :helper="__('settings.sponsorship_reminders_helper')" onChange="instantSave" :options="[
                                ['value' => true, 'label' => __('profile.enabled')],
                                ['value' => false, 'label' => __('common.disabled')],
                            ]" />
                        @if (isDev())
                            <x-forms.button type="button" @click="$dispatch('show-sponsorship-reminder')">
                                {{ __('settings.show_sponsorship_reminder') }}
                            </x-forms.button>
                        @endif
                    </div>
                </div>
            </x-application.settings-section>

            <x-application.settings-section id="avatar-storage-section" :title="__('settings.image_storage')"
                :helper="__('settings.image_storage_helper')">
                <div class="flex max-w-md flex-col gap-4">
                    <x-forms.listbox id="avatar_storage" :label="__('settings.storage_destination')" onChange="instantSave"
                        :options="$avatar_storage_options" />
                    <x-forms.input id="image_cdn_url" :label="__('settings.image_cdn_url')"
                        :helper="__('settings.image_cdn_url_helper')" placeholder="https://images.example.com" />
                </div>
                @if (count($avatar_storage_options) === 1)
                    <x-callout type="info" :title="__('settings.no_usable_s3')" class="mt-4">
                        {{ __('settings.add_s3') }}
                    </x-callout>
                @endif
            </x-application.settings-section>
        </form>
    </x-settings.layout>
</div>
