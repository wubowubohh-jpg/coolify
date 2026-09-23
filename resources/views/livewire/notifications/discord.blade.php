<div>
    <x-slot:title>
        {{ __('common.notifications') }} - {{ __('common.discord') }} | Coolify
    </x-slot>

    <x-notification.settings-layout>
    <div class="application-settings-form flex flex-col gap-6">
        <form wire:submit="submit">
            <x-unsaved-bar action="submit" />
            <x-application.settings-section :title="__('common.discord')"
                :description="__('notifications.discord_description')">
                <x-slot:actions>
                    <x-notification.channel-actions :enabled="$discordEnabled" enabledProperty="discordEnabled"
                        toggleMethod="instantSaveDiscordEnabled" :canUpdate="auth()->user()->can('update', $settings)" />
                </x-slot:actions>

                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.listbox canGate="update" :canResource="$settings" id="discordPingEnabled"
                        :label="__('notifications.critical_event_mention')"
                        :helper="__('notifications.critical_event_mention_helper')"
                        onChange="instantSaveDiscordPingEnabled"
                        :disabled="!auth()->user()->can('update', $settings)" :options="[
                            ['value' => true, 'label' => __('notifications.mention_here')],
                            ['value' => false, 'label' => __('notifications.do_not_mention')],
                        ]" />
                    <div class="lg:col-span-2">
                        @can('update', $settings)
                            <x-forms.input type="password" required id="discordWebhookUrl"
                                :label="__('notifications.webhook_url')"
                                :helper="__('notifications.discord_webhook_helper')" />
                        @else
                            <x-forms.input disabled :label="__('notifications.webhook_url')"
                                :value="__('common.hidden_admins_only')" />
                        @endcan
                    </div>
                </div>
            </x-application.settings-section>
        </form>

        <x-notification.event-grid :settings="$settings" channel="discord" />
    </div>
    </x-notification.settings-layout>
</div>
