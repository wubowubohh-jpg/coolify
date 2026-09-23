<div>
    <x-slot:title>
        {{ __('common.notifications') }} - {{ __('common.pushover') }} | Coolify
    </x-slot>

    <x-notification.settings-layout>
    <div class="application-settings-form flex flex-col gap-6">
        <form wire:submit="submit">
            <x-unsaved-bar action="submit" />
            <x-application.settings-section :title="__('common.pushover')"
                :description="__('notifications.pushover_description')">
                <x-slot:actions>
                    <x-notification.channel-actions :enabled="$pushoverEnabled" enabledProperty="pushoverEnabled"
                        toggleMethod="instantSavePushoverEnabled" :canUpdate="auth()->user()->can('update', $settings)" />
                </x-slot:actions>

                <div class="grid gap-4 lg:grid-cols-2">
                    @can('update', $settings)
                        <x-forms.input type="password" required id="pushoverUserKey"
                            :label="__('notifications.user_key')"
                            :helper="__('notifications.user_key_helper')" />
                        <x-forms.input type="password" required id="pushoverApiToken"
                            :label="__('notifications.api_token')"
                            :helper="__('notifications.pushover_api_token_helper')" />
                    @else
                        <x-forms.input disabled :label="__('notifications.user_key')"
                            :value="__('common.hidden_admins_only')" />
                        <x-forms.input disabled :label="__('notifications.api_token')"
                            :value="__('common.hidden_admins_only')" />
                    @endcan
                </div>
            </x-application.settings-section>
        </form>

        <x-notification.event-grid :settings="$settings" channel="pushover" />
    </div>
    </x-notification.settings-layout>
</div>
