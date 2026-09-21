@php use App\Enums\ProxyTypes; @endphp

<div class="application-settings-form flex w-full flex-col gap-6">
    @if ($server->proxyType())
        @if ($selectedProxy !== 'NONE')
            <form wire:submit="submit" class="contents">
                <x-unsaved-bar action="submit" />

                <fieldset class="contents" wire:loading.attr="disabled"
                    wire:target="submit,resetProxyConfiguration">

                <x-application.settings-section id="server-proxy-overview-section" :title="__('common.proxy_configuration')"
                    :helper="__('common.proxy_configuration_helper')">
                    <x-slot:actions>
                        <div class="flex items-center gap-2">
                            <x-status-badge :status="str($server->proxy->status)->headline()"
                                :type="str($server->proxy->status)->contains('running') ? 'success' : 'neutral'" />
                            @if ($server->proxy->status === 'exited' || $server->proxy->status === 'removing')
                                @can('update', $server)
                                    <x-modal-confirmation :title="__('common.confirm_proxy_switch')"
                                        :buttonTitle="__('common.switch_proxy')" submitAction="changeProxy"
                                        :actions="[__('common.proxy_switch_reset_warning')]"
                                        :warningMessage="__('common.review_proxy_switch_guide')"
                                        :step2ButtonText="__('common.switch_proxy')" :confirmWithText="false"
                                        :confirmWithPassword="false" />
                                @endcan
                            @else
                                <x-forms.button canGate="update" :canResource="$server"
                                    wire:click="$dispatch('error', @js(__('common.proxy_running_stop_before_switch')))" >
                                    {{ __('common.switch_proxy') }}
                                </x-forms.button>
                            @endif
                        </div>
                    </x-slot:actions>

                    @if (
                        $server->proxy->last_applied_settings &&
                            $server->proxy->last_saved_settings !== $server->proxy->last_applied_settings)
                        <x-callout type="warning" :title="__('common.configuration_out_of_sync')">
                            {{ __('common.restart_proxy_to_apply') }}
                        </x-callout>
                    @else
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-neutral-500 dark:bg-white/[0.06] dark:text-fg-dim">
                                <x-reicon name="servers" class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-950 dark:text-fg">
                                    {{ str($server->proxyType())->title() }}
                                </p>
                                <p class="mt-1 text-xs text-neutral-500 dark:text-fg-dim">
                                    {{ __('common.saved_running_synchronized') }}
                                </p>
                            </div>
                        </div>
                    @endif
                </x-application.settings-section>

                <x-application.settings-section id="server-proxy-routing-section" :title="__('common.routing_behavior')"
                    :helper="__('common.routing_behavior_helper')">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-forms.listbox id="generateExactLabels" :label="__('common.generated_labels')"
                            :helper="__('common.generated_labels_helper')"
                            onChange="instantSave" :options="[
                                ['value' => false, 'label' => __('common.labels_all_supported_proxies')],
                                ['value' => true, 'label' => __('common.labels_active_proxy_only')],
                            ]" />
                        <x-forms.listbox id="redirectEnabled" :label="__('common.unknown_requests')"
                            :helper="__('common.unknown_requests_helper')"
                            onChange="instantSaveRedirect" :options="[
                                ['value' => false, 'label' => __('common.default_503_response')],
                                ['value' => true, 'label' => __('common.custom_request_handling')],
                            ]" />
                        @if ($redirectEnabled)
                            <x-forms.input canGate="update" :canResource="$server"
                                placeholder="https://app.coolify.io" id="redirectUrl"
                                :label="__('common.redirect_url')"
                                :helper="__('common.redirect_url_helper')" />
                        @endif
                    </div>
                </x-application.settings-section>

                @php
                    $proxyTitle =
                        $server->proxyType() === ProxyTypes::TRAEFIK->value
                            ? __('common.traefik_configuration')
                            : __('common.caddy_configuration');
                @endphp

                @if ($server->proxyType() === ProxyTypes::TRAEFIK->value || $server->proxyType() === 'CADDY')
                    <x-application.settings-section id="server-proxy-file-section" :title="$proxyTitle"
                        :helper="__('common.proxy_compose_configuration_helper')">
                        <x-slot:actions>
                            @can('update', $server)
                                @if ($proxySettings)
                                    <x-modal-confirmation :title="__('common.reset_proxy_configuration')"
                                        :buttonTitle="__('common.reset_configuration')"
                                        submitAction="resetProxyConfiguration" :actions="[
                                            __('common.reset_proxy_configuration_action'),
                                            __('common.remove_custom_proxy_changes'),
                                        ]" confirmationText="{{ $server->name }}"
                                        :confirmationLabel="__('common.confirm_server_name')"
                                        :shortConfirmationLabel="__('common.server_name')"
                                        :step2ButtonText="__('common.reset_configuration')"
                                        :confirmWithPassword="false" :confirmWithText="true" />
                                @endif
                            @endcan
                        </x-slot:actions>

                        @if ($server->proxyType() === ProxyTypes::TRAEFIK->value)
                            @if ($server->detected_traefik_version === 'latest')
                                <x-callout type="warning" :title="__('common.unpinned_traefik_version')">
                                    {{ __('common.unpinned_traefik_version_description') }}
                                    <span class="font-mono">traefik:{{ $this->latestTraefikVersion }}</span>
                                    {{ __('common.for_predictable_updates') }}
                                </x-callout>
                            @elseif($this->isTraefikOutdated)
                                <x-callout type="warning" :title="__('common.traefik_patch_update')">
                                    {{ __('common.traefik_patch_update_description', ['version' => $this->latestTraefikVersion]) }}
                                </x-callout>
                            @elseif($this->newerTraefikBranchAvailable)
                                <x-callout type="info" :title="__('common.traefik_minor_update')">
                                    {{ __('common.traefik_minor_update_description', ['version' => $this->newerTraefikBranchAvailable]) }}
                                </x-callout>
                            @endif
                        @endif

                        @if ($proxySettings)
                            <div class="relative mt-4" wire:loading.class="pointer-events-none opacity-50"
                                wire:target="submit,resetProxyConfiguration" aria-live="polite">
                                <div wire:loading.flex wire:target="submit,resetProxyConfiguration"
                                    class="absolute inset-0 z-20 hidden items-center justify-center rounded-lg bg-white/75 backdrop-blur-[1px] dark:bg-black/55">
                                    <div
                                        class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-xs font-medium text-neutral-700 shadow-sm ring-1 ring-neutral-200 dark:bg-coolgray-100 dark:text-fg dark:ring-white/10">
                                        <x-loading />
                                        {{ __('common.updating_proxy_configuration') }}
                                    </div>
                                </div>
                                <x-forms.textarea canGate="update" :canResource="$server" useMonacoEditor
                                    monacoEditorLanguage="yaml"
                                    :label="__('common.configuration_file', ['path' => $this->configurationFilePath])"
                                    name="proxySettings" id="proxySettings" rows="30" />
                            </div>
                        @endif
                    </x-application.settings-section>
                @endif
                </fieldset>
            </form>
        @elseif($selectedProxy === 'NONE')
            <x-application.settings-section :title="__('common.custom_proxy')"
                :helper="__('common.custom_proxy_helper')">
                <x-slot:actions>
                    @can('update', $server)
                        <x-forms.button wire:click.prevent="changeProxy">{{ __('common.switch_proxy') }}</x-forms.button>
                    @endcan
                </x-slot:actions>
                <x-callout type="info" :title="__('common.custom_proxy_selected')">
                    {{ __('common.configure_proxy_outside_coolify') }}
                </x-callout>
            </x-application.settings-section>
        @else
            <x-application.settings-section :title="__('common.proxy_configuration')"
                :helper="__('common.choose_proxy_implementation')">
                @can('update', $server)
                    <div class="grid gap-3 lg:grid-cols-3">
                        @foreach ([
                            ['value' => 'NONE', 'title' => __('common.custom'), 'description' => __('common.manage_proxy_outside_coolify')],
                            ['value' => 'TRAEFIK', 'title' => 'Traefik', 'description' => __('common.use_default_coolify_proxy')],
                            ['value' => 'CADDY', 'title' => 'Caddy', 'description' => __('common.use_coolify_caddy_integration')],
                        ] as $proxyOption)
                            <button type="button" wire:click="selectProxy('{{ $proxyOption['value'] }}')"
                                class="rounded-lg p-4 text-left ring-1 ring-neutral-200 transition-colors hover:bg-neutral-50 dark:ring-white/[0.08] dark:hover:bg-white/[0.04]">
                                <p class="text-sm font-medium text-neutral-950 dark:text-fg">
                                    {{ $proxyOption['title'] }}
                                </p>
                                <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                                    {{ $proxyOption['description'] }}
                                </p>
                            </button>
                        @endforeach
                    </div>
                @else
                    <x-callout type="danger" :title="__('common.insufficient_permissions')">
                        {{ __('common.no_permission_select_proxy') }}
                    </x-callout>
                @endcan
            </x-application.settings-section>
        @endif
    @else
        <x-application.settings-section :title="__('common.proxy_configuration')"
            :helper="__('common.choose_proxy_implementation')">
            @can('update', $server)
                <div class="grid gap-3 lg:grid-cols-3">
                    @foreach ([
                        ['value' => 'NONE', 'title' => __('common.custom'), 'description' => __('common.manage_proxy_outside_coolify')],
                        ['value' => 'TRAEFIK', 'title' => 'Traefik', 'description' => __('common.use_default_coolify_proxy')],
                        ['value' => 'CADDY', 'title' => 'Caddy', 'description' => __('common.use_coolify_caddy_integration')],
                    ] as $proxyOption)
                        <button type="button" wire:click="selectProxy('{{ $proxyOption['value'] }}')"
                            class="rounded-lg p-4 text-left ring-1 ring-neutral-200 transition-colors hover:bg-neutral-50 dark:ring-white/[0.08] dark:hover:bg-white/[0.04]">
                            <p class="text-sm font-medium text-neutral-950 dark:text-fg">{{ $proxyOption['title'] }}</p>
                            <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                                {{ $proxyOption['description'] }}
                            </p>
                        </button>
                    @endforeach
                </div>
            @endcan
        </x-application.settings-section>
    @endif
</div>
