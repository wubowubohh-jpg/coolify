<div>
    <x-slot:title>
        {{ __('settings.instance_settings') }} | Coolify
    </x-slot>

    <x-settings.layout>
        <form wire:submit="submit" class="application-settings-form flex w-full min-w-0 flex-col gap-6">
            {{-- instance_timezone auto-saves via $wire.set + submit; exclude it so
                 the bar does not flash while the snapshot catches up. --}}
            <x-unsaved-bar action="submit"
                targets="fqdn,instance_name,public_ipv4,public_ipv6,dev_helper_version" />
            <x-application.settings-section :title="__('settings.general')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <div @class([
                        'lg:col-span-2' => !str_starts_with(strtolower($fqdn ?? ''), 'https://'),
                    ])>
                        <x-forms.input canGate="update" :canResource="$settings" id="fqdn" :label="__('settings.url')"
                            :helper="__('settings.url_helper')"
                            placeholder="https://coolify.yourdomain.com" />
                    </div>

                    @if (str_starts_with(strtolower($fqdn ?? ''), 'https://'))
                        <div>
                            <x-forms.listbox canGate="update" :canResource="$settings"
                                id="is_dashboard_force_https_enabled" :label="__('settings.redirect_http_to_https')"
                                onChange="submit"
                                :helper="__('settings.redirect_http_helper')"
                                :options="[
                                    ['value' => true, 'label' => __('profile.enabled')],
                                    ['value' => false, 'label' => __('common.disabled')],
                                ]" />
                        </div>
                    @endif

                    <x-forms.input canGate="update" :canResource="$settings" id="instance_name" :label="__('settings.name')"
                        placeholder="Coolify" :helper="__('settings.name_helper')" />

                    {{-- Use searchable-listbox so the label row (h-4) and control height match
                         sibling x-forms.input fields (Name). onChange auto-saves like before. --}}
                    <x-forms.searchable-listbox id="instance_timezone" :label="__('settings.instance_timezone')"
                        :helper="__('settings.instance_timezone_helper')"
                        :searchPlaceholder="__('settings.search_timezones')" :emptyText="__('settings.no_matching_timezone')"
                        onChange="submit" :options="collect($this->timezones)->map(fn ($timezone) => [
                            'value' => $timezone,
                            'label' => $timezone,
                        ])->all()" :disabled="! auth()->user()->can('update', $settings)" />
                </div>
            </x-application.settings-section>

            <x-application.settings-section :title="__('settings.network_addresses')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.input canGate="update" :canResource="$settings" id="public_ipv4" type="password"
                        :label="__('settings.instance_public_ipv4')"
                        :helper="__('settings.public_ipv4_helper')"
                        placeholder="1.2.3.4" autocomplete="new-password" />
                    <x-forms.input canGate="update" :canResource="$settings" id="public_ipv6" type="password"
                        :label="__('settings.instance_public_ipv6')"
                        :helper="__('settings.public_ipv6_helper')"
                        placeholder="2001:db8::1" autocomplete="new-password" />
                </div>
            </x-application.settings-section>

            @if (isDev())
                <x-application.settings-section :title="__('settings.development_helper')">
                    <x-forms.input canGate="update" :canResource="$settings" id="dev_helper_version"
                        :label="__('settings.version_override')"
                        :helper="__('settings.version_override_helper', ['version' => config('constants.coolify.helper_version')])"
                        placeholder="{{ config('constants.coolify.helper_version') }}" />
                </x-application.settings-section>
            @endif
        </form>

    <x-domain-conflict-modal :conflicts="$domainConflicts" :showModal="$showDomainConflictModal"
        confirmAction="confirmDomainUsage">
        <x-slot:consequences>
            <ul class="mt-2 ml-4 list-disc">
                <li>{{ __('settings.domain_conflict_instance') }}</li>
                <li>{{ __('settings.domain_conflict_ssl') }}</li>
                <li>{{ __('settings.domain_conflict_routing') }}</li>
                <li>{{ __('settings.domain_conflict_dashboard') }}</li>
            </ul>
        </x-slot:consequences>
    </x-domain-conflict-modal>
    </x-settings.layout>
</div>
