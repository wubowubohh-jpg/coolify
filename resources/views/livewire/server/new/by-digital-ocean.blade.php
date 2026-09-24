<div class="w-full">
    @if ($limit_reached)
        <x-limit-reached name="servers" />
    @elseif ($current_step === 1)
        <div class="flex flex-col gap-6">
            <x-server.provider-token-picker provider="digitalocean" providerLabel="DigitalOcean"
                routeType="digital-ocean" :tokens="$available_tokens" />
            <p class="text-[11px] text-neutral-500 dark:text-fg-faint">
                {{ __('common.new_to_provider', ['provider' => 'DigitalOcean']) }}
                <a href="https://coolify.io/digitalocean" target="_blank"
                    class="font-medium text-coollabs hover:underline dark:text-warning">{{ __('common.create_account') }}</a>
                {{ __('common.through_referral_link') }}
            </p>
        </div>
    @elseif ($current_step === 2)
        <div wire:init="loadDigitalOceanData">
            @if ($loading_data)
                <x-application.settings-section :title="__('common.loading_digitalocean')"
                    :description="__('common.fetching_digitalocean_resources')">
                    <div class="flex min-h-40 items-center justify-center">
                        <x-loading :text="__('common.loading_digitalocean_data')" />
                    </div>
                </x-application.settings-section>
            @elseif ($provider_data_error)
                <x-application.settings-section :title="__('common.unable_to_load_provider', ['provider' => 'DigitalOcean'])"
                    :description="__('common.provider_token_api_error')">
                    <x-callout type="error" :title="__('common.provider_request_failed')">
                        <pre class="mt-2 whitespace-pre-wrap break-words text-[11px]">{{ $provider_data_error }}</pre>
                    </x-callout>
                    <div class="mt-4">
                        <a class="button"
                            href="{{ route('server.create.type', ['type' => 'digital-ocean']) }}"
                            {{ wireNavigate() }}>{{ __('common.select_another_token') }}</a>
                    </div>
                </x-application.settings-section>
            @else
                @php
                    $regionOptions = collect($regions)->map(fn ($region) => [
                        'value' => $region['slug'],
                        'label' => $region['name'] ?? $region['slug'],
                    ])->values()->all();
                    $sizeOptions = collect($this->availableSizes)->map(function ($size) {
                        $label = $size['slug']
                            . ' · ' . ($size['memory'] ?? '?') . ' MB RAM'
                            . ' · ' . ($size['vcpus'] ?? '?') . ' vCPU'
                            . (isset($size['disk']) ? ' · ' . $size['disk'] . ' GB' : '');
                        return [
                            'value' => $size['slug'],
                            'label' => isset($size['price_monthly'])
                                ? $label . ' · $' . number_format((float) $size['price_monthly'], 2) . '/mo'
                                : $label,
                        ];
                    })->values()->all();
                    $imageOptions = collect($this->availableImages)->map(fn ($image) => [
                        'value' => $image['slug'] ?? $image['id'],
                        'label' => trim(($image['distribution'] ?? '') . ' ' . ($image['name'] ?? $image['slug'] ?? $image['id'])),
                    ])->values()->all();
                    $privateKeyOptions = $private_keys->map(fn ($key) => [
                        'value' => $key->id,
                        'label' => $key->name,
                    ])->values()->all();
                    $scriptOptions = collect([
                        ['value' => '', 'label' => __('common.start_with_empty_script')],
                        ...$saved_cloud_init_scripts->map(fn ($script) => [
                            'value' => $script->id,
                            'label' => $script->name,
                        ])->all(),
                    ])->all();
                @endphp

                <form wire:submit="submit" class="flex flex-col gap-6">
                    <x-application.settings-section :title="__('common.digitalocean_droplet')"
                        :description="__('common.digitalocean_setup_description')">
                        <x-slot:actions>
                            <button type="submit"
                                class="button button-highlighted"
                                @disabled(!$private_key_id)>
                                {{ __('common.buy_and_create') }}
                                @if ($this->selectedDropletPrice)
                                    <span class="opacity-70">· {{ $this->selectedDropletPrice }}/mo</span>
                                @endif
                            </button>
                        </x-slot:actions>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div class="lg:col-span-2">
                                <x-forms.input id="server_name" :label="__('common.server_name')"
                                    :helper="__('common.friendly_server_name_helper')" />
                            </div>
                            <x-forms.listbox id="selected_region" :label="__('common.region')" required live
                                :placeholder="__('common.select_region')" :options="$regionOptions" />
                            <x-forms.listbox id="selected_size" :label="__('common.server_size')" required live
                                :disabled="!$selected_region" :placeholder="__('common.select_size')"
                                :options="$sizeOptions" />
                            <x-forms.listbox id="selected_image" :label="__('common.image')" required
                                :disabled="!$selected_size" :placeholder="__('common.select_image')"
                                :options="$imageOptions" />
                            @if ($private_keys->isEmpty())
                                <div>
                                    <label class="mb-1.5 flex w-fit items-center gap-1.5">{{ __('common.private_key') }}
                                        <x-highlighted text="*" />
                                    </label>
                                    <div
                                        class="flex min-h-8 items-center justify-between gap-3 rounded-lg border border-warning/30 bg-warning/5 px-3 py-2">
                                        <span class="text-[11px] text-neutral-600 dark:text-fg-dim">{{ __('common.private_key_required') }}</span>
                                        <x-modal-input :title="__('common.new_private_key')">
                                            <x-slot:content>
                                                <button type="button" class="button">{{ __('common.create_key') }}</button>
                                            </x-slot:content>
                                            <livewire:security.private-key.create :modal_mode="true" from="server" />
                                        </x-modal-input>
                                    </div>
                                </div>
                            @else
                                <x-forms.listbox id="private_key_id" :label="__('common.private_key')" required
                                    :placeholder="__('common.select_private_key')" :options="$privateKeyOptions"
                                    :helper="__('common.provider_private_key_helper', ['provider' => 'DigitalOcean'])" />
                            @endif
                        </div>
                    </x-application.settings-section>

                    <x-application.settings-section :title="__('common.advanced_options')"
                        :description="__('common.provider_ssh_networking_monitoring_cloud_init')">
                        @if (count($this->advancedDigitalOceanOptionsSummary) > 0)
                            <div class="mb-4 flex flex-wrap gap-1.5">
                                @foreach ($this->advancedDigitalOceanOptionsSummary as $summaryItem)
                                    <span
                                        class="rounded-full bg-neutral-100 px-2 py-0.5 text-[10px] font-medium text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
                                        {{ $summaryItem }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex flex-col gap-4">
                            <x-forms.datalist :label="__('common.extra_ssh_keys')" id="selectedDigitalOceanSshKeyIds"
                                :helper="__('common.existing_provider_keys_helper', ['provider' => 'DigitalOcean'])" :multiple="true"
                                :disabled="count($digitalOceanSshKeys) === 0"
                                :placeholder="count($digitalOceanSshKeys) ? __('common.search_ssh_keys') : __('common.no_account_keys_found')">
                                @foreach ($digitalOceanSshKeys as $sshKey)
                                    <option value="{{ $sshKey['id'] }}">
                                        {{ $sshKey['name'] ?? $sshKey['fingerprint'] }}
                                    </option>
                                @endforeach
                            </x-forms.datalist>

                            <div class="grid gap-3 lg:grid-cols-2">
                                <x-forms.checkbox id="enable_ipv6" :label="__('common.enable_ipv6')" fullWidth />
                                <x-forms.checkbox id="monitoring" :label="__('common.enable_provider_monitoring', ['provider' => 'DigitalOcean'])"
                                    fullWidth />
                            </div>

                            <div class="border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                                @if (!$show_cloud_init_script && blank($cloud_init_script) && blank($selected_cloud_init_script_id))
                                    <button type="button" class="button" wire:click="showCloudInitScript">
                                        <x-reicon name="plus" class="size-3.5" />
                                        {{ __('common.add_cloud_init_script') }}
                                    </button>
                                @else
                                    <div class="flex flex-col gap-4">
                                        <div class="grid items-end gap-3 lg:grid-cols-[minmax(0,1fr)_auto]">
                                            <x-forms.listbox id="selected_cloud_init_script_id"
                                                :label="__('common.saved_cloud_init_script')" live :options="$scriptOptions" />
                                            <button type="button" class="button"
                                                wire:click="clearCloudInitScript">{{ __('common.clear') }}</button>
                                        </div>
                                        <x-forms.textarea id="cloud_init_script" :label="__('common.cloud_init_script')"
                                            rows="8" monospace />
                                        <div class="grid items-end gap-4 lg:grid-cols-2">
                                            <x-forms.checkbox id="save_cloud_init_script"
                                                :label="__('common.save_script_for_later')" />
                                            @if ($save_cloud_init_script)
                                                <x-forms.input id="cloud_init_script_name" :label="__('common.saved_script_name')" />
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-application.settings-section>
                </form>
            @endif
        </div>
    @endif
</div>
