<div>
    <x-slot:title>
        {{ __('common.proxy_dynamic_configuration') }} | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="proxy" activeSubMenu="dynamic-confs" />

        <div class="application-settings-form flex w-full flex-col gap-6">
            @if ($server->isFunctional())
                <div class="flex flex-wrap items-start justify-between gap-3 px-1">
                    <div>
                        <h2 class="text-sm! font-medium text-neutral-950 dark:text-fg">
                            {{ __('common.dynamic_configurations') }}
                        </h2>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-fg-dim">
                            {{ __('common.dynamic_configurations_description') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-forms.button wire:click="loadDynamicConfigurations">
                            <x-reicon name="refresh" class="size-3.5" />
                            {{ __('common.reload') }}
                        </x-forms.button>
                        @can('update', $server)
                            <x-modal-input :buttonTitle="__('common.add')" :title="__('common.new_dynamic_configuration')">
                                <livewire:server.proxy.new-dynamic-configuration :server_id="$server->id" />
                            </x-modal-input>
                        @endcan
                    </div>
                </div>

                <div x-init="$wire.initLoadDynamicConfigurations" class="contents">
                    <div wire:loading wire:target="initLoadDynamicConfigurations"
                        class="rounded-lg border border-neutral-200 p-6 dark:border-white/[0.08]">
                        <x-loading :text="__('common.loading_dynamic_configurations')" />
                    </div>

                    @if ($contents?->isNotEmpty())
                        @foreach ($contents as $fileName => $value)
                            @php
                                $displayName = str_replace('|', '.', $fileName);
                                $isManagedConfiguration = in_array($displayName, [
                                    'coolify.yaml',
                                    'Caddyfile',
                                    'coolify.caddy',
                                    'default_redirect_503.yaml',
                                    'default_redirect_503.caddy',
                                ]);
                            @endphp
                            <x-application.settings-section :title="$displayName"
                                wire:key="proxy-dynamic-configuration-{{ $fileName }}">
                                <x-slot:actions>
                                    @if ($isManagedConfiguration)
                                        <x-status-badge :status="__('common.managed_by_coolify')" type="neutral" />
                                    @else
                                        <livewire:server.proxy.dynamic-configuration-navbar
                                            :server_id="$server->id" :server="$server" :fileName="$fileName"
                                            :value="$value ?? ''" :newFile="false"
                                            wire:key="proxy-navbar-{{ $fileName }}" />
                                    @endif
                                </x-slot:actions>
                                <x-forms.textarea disabled wire:model="contents.{{ $fileName }}"
                                    rows="8" />
                            </x-application.settings-section>
                        @endforeach
                    @else
                        <x-application.settings-section wire:loading.remove :title="__('common.dynamic_configurations')">
                            <x-empty size="sm" :title="__('common.no_dynamic_configurations')"
                                :description="__('common.add_dynamic_configuration_description')"
                                icon-name="file-content" />
                        </x-application.settings-section>
                    @endif
                </div>
            @else
                <x-application.settings-section :title="__('common.dynamic_configurations')"
                    :helper="__('common.dynamic_configurations_description')">
                    <x-empty size="sm" :title="__('common.server_validation_required')"
                        :description="__('common.validate_server_before_proxy_configuration')"
                        icon-name="file-content" />
                </x-application.settings-section>
            @endif
        </div>
    </div>
</div>
