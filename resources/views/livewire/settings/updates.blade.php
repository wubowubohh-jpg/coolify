<div>
    <x-slot:title>
        {{ __('settings.updates') }} | Coolify
    </x-slot>

    <x-settings.layout>
        <form wire:submit="submit" class="application-settings-form flex min-w-0 flex-col gap-6">
            {{-- Exclude is_auto_update_enabled (instantSave) so the bar does not flash. --}}
            <x-unsaved-bar action="submit" targets="update_check_frequency,auto_update_frequency" />

            <x-application.settings-section :title="__('settings.update_coolify')"
                :helper="__('settings.update_coolify_helper')">
                <livewire:upgrade :full-button="true" key="settings-upgrade" />
            </x-application.settings-section>

            <x-application.settings-section :title="__('settings.update_checks')">
                <x-slot:actions>
                    <x-forms.button type="button" wire:click="checkManually">
                        <x-reicon name="refresh" class="size-3.5" />
                        {{ __('settings.check_now') }}
                    </x-forms.button>
                </x-slot:actions>
                <x-forms.input required id="update_check_frequency" :label="__('settings.check_frequency')"
                    placeholder="0 * * * *"
                    :helper="__('settings.check_frequency_helper')" />
            </x-application.settings-section>

            <x-application.settings-section :title="__('settings.automatic_updates')">
                <div class="grid gap-4 lg:grid-cols-2">
                    @if (!is_null(config('constants.coolify.autoupdate', null)))
                        <x-forms.listbox disabled id="is_auto_update_enabled" :label="__('settings.automatic_updates')"
                            :helper="__('settings.automatic_updates_helper')" :options="[
                                ['value' => true, 'label' => __('profile.enabled')],
                                ['value' => false, 'label' => __('common.disabled')],
                            ]" />
                    @else
                        <x-forms.listbox id="is_auto_update_enabled" :label="__('settings.automatic_updates')"
                            onChange="instantSave" :options="[
                                ['value' => true, 'label' => __('profile.enabled')],
                                ['value' => false, 'label' => __('common.disabled')],
                            ]" />
                    @endif

                    @if (is_null(config('constants.coolify.autoupdate', null)) && $is_auto_update_enabled)
                        <x-forms.input required id="auto_update_frequency" :label="__('settings.update_frequency')"
                            placeholder="0 0 * * *"
                            :helper="__('settings.update_frequency_helper')" />
                    @else
                        <x-forms.input :label="__('settings.update_frequency')" disabled :placeholder="__('common.disabled')" />
                    @endif
                </div>
            </x-application.settings-section>

            <x-application.settings-section :title="__('settings.image_registry')">
                <div class="max-w-md">
                    <x-forms.listbox id="docker_registry_url" :label="__('settings.docker_registry')" :options="[
                        ['value' => 'docker.io', 'label' => __('settings.docker_hub')],
                        ['value' => 'ghcr.io', 'label' => __('settings.github_container_registry')],
                    ]"
                        :helper="__('settings.registry_helper')" />
                </div>
            </x-application.settings-section>
        </form>
    </x-settings.layout>
</div>
