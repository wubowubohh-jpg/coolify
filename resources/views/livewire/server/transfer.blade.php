<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > {{ __('common.transfer') }} | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="transfer" />

        <div class="application-settings-form flex w-full flex-col gap-6">
            @if ($this->isLocalhost)
                <x-application.settings-section id="server-transfer-section" :title="__('common.transfer_server_dev')"
                    :helper="__('common.transfer_server_helper')">
                    <x-callout type="warning" :title="__('common.localhost_cannot_transfer')">
                        {{ __('common.localhost_transfer_description') }}
                    </x-callout>
                </x-application.settings-section>
            @else
                <x-application.settings-section id="server-transfer-section" :title="__('common.transfer_server_dev')"
                    :helper="__('common.transfer_server_helper')">
                    <x-slot:actions>
                        <x-status-badge
                            :label="$this->transferStatus ?: __('common.ready')"
                            type="neutral" />
                        @if ($exportId)
                            <span class="text-[11px] text-neutral-500 dark:text-fg-faint">{{ __('common.export_id', ['id' => $exportId]) }}</span>
                        @endif
                    </x-slot:actions>

                    <div class="flex flex-col gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-neutral-950 dark:text-fg">{{ __('common.transfer_to_instance') }}
                            </h3>
                            <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                                {{ __('common.transfer_to_instance_description') }}
                            </p>
                        </div>
                        <div class="flex flex-col gap-3 md:max-w-xl">
                            <x-forms.input id="targetUrl" :label="__('common.target_instance_url')" required
                                placeholder="http://localhost:8001"
                                :helper="__('common.target_instance_url_helper')" />
                            <x-forms.input id="targetToken" type="password" :label="__('common.target_api_token')" required
                                :placeholder="__('common.target_api_token_placeholder')" autocomplete="off"
                                :helper="__('common.target_api_token_helper')" />
                            <x-forms.checkbox id="writeRemote"
                                :label="__('common.write_ownership_file_optional')" />
                        </div>
                        <div>
                            <x-forms.button canGate="update" :canResource="$server" wire:click="migrateServer"
                                wire:loading.attr="disabled"
                                wire:confirm="{{ __('common.transfer_server_confirmation') }}">
                                <span wire:loading.remove wire:target="migrateServer">{{ __('common.transfer_server') }}</span>
                                <span wire:loading wire:target="migrateServer">{{ __('common.transferring') }}</span>
                            </x-forms.button>
                        </div>
                        @if (count($lastWarnings) > 0)
                            <x-callout type="warning" :title="__('common.warnings')">
                                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                                    @foreach ($lastWarnings as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </x-callout>
                        @endif
                        @if ($lastResultJson)
                            <div>
                                <div class="mb-1 text-sm font-semibold">{{ __('common.result') }}</div>
                                <pre
                                    class="max-h-64 overflow-auto rounded-lg bg-neutral-100 p-3 text-xs dark:bg-coolgray-100">{{ $lastResultJson }}</pre>
                            </div>
                        @endif
                    </div>
                </x-application.settings-section>

                <x-application.settings-section id="server-transfer-advanced-section" :title="__('common.advanced')"
                    :helper="__('common.transfer_advanced_helper')">
                    <div class="flex flex-col gap-6" x-data="{ open: @entangle('showAdvanced') }">
                        <button type="button"
                            class="flex w-full items-center justify-between rounded-lg border border-neutral-200 px-4 py-3 text-left dark:border-white/[0.08]"
                            @click="open = !open">
                            <span class="text-sm font-semibold">{{ __('common.show_advanced_options') }}</span>
                            <span class="text-xs text-neutral-500 dark:text-fg-faint"
                                x-text="open ? @js(__('common.hide')) : @js(__('common.show'))"></span>
                        </button>

                        <div class="flex flex-col gap-6" x-show="open" x-cloak>
                            <div>
                                <h4 class="text-sm font-medium">{{ __('common.download_bundle') }}</h4>
                                <p class="mb-3 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                                    {{ __('common.download_bundle_description') }}
                                </p>
                                <div class="mb-3 flex flex-col gap-3 md:max-w-xl">
                                    <x-forms.checkbox id="encryptBundle" :label="__('common.encrypt_with_passphrase')" />
                                    <x-forms.input id="passphrase" type="password" :label="__('common.passphrase')"
                                        :placeholder="__('common.passphrase_placeholder')" autocomplete="new-password" />
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <x-forms.button canGate="view" :canResource="$server" wire:click="exportBundle"
                                        wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="exportBundle">{{ __('common.download_json') }}</span>
                                        <span wire:loading wire:target="exportBundle">{{ __('common.exporting') }}</span>
                                    </x-forms.button>
                                    <a href="{{ route('server.transfer.import') }}" {{ wireNavigate() }}
                                        class="button">
                                        {{ __('common.import_page_this_instance') }}
                                    </a>
                                </div>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium">{{ __('common.complete_only') }}</h4>
                                <p class="mb-3 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                                    {{ __('common.complete_only_description') }}
                                </p>
                                <x-forms.button canGate="update" :canResource="$server" wire:click="completeTransfer"
                                    wire:loading.attr="disabled"
                                    wire:confirm="{{ __('common.disable_automations_confirmation') }}">
                                    {{ __('common.mark_transferred_disable_automations') }}
                                </x-forms.button>
                            </div>

                            <div>
                                <h4 class="text-sm font-medium">{{ __('common.reclaim_only') }}</h4>
                                <p class="mb-3 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                                    {{ __('common.reclaim_only_description') }}
                                </p>
                                <div class="mb-3 flex flex-col gap-2">
                                    <x-forms.checkbox id="writeRemoteOnClaim" :label="__('common.write_ownership_file_ssh')" />
                                    <x-forms.checkbox id="rebindSentinelOnClaim" :label="__('common.rebind_sentinel')" />
                                </div>
                                <x-forms.button canGate="update" :canResource="$server" wire:click="claimServer"
                                    wire:loading.attr="disabled">
                                    {{ __('common.reclaim') }}
                                </x-forms.button>
                            </div>
                        </div>
                    </div>
                </x-application.settings-section>
            @endif
        </div>
    </div>
</div>
