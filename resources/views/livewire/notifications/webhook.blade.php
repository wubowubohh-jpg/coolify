<div>
    <x-slot:title>
        {{ __('common.notifications') }} - {{ __('common.webhook') }} | Coolify
    </x-slot>

    <x-notification.settings-layout>
    <div class="application-settings-form flex flex-col gap-6">
        <form wire:submit="submit">
            <x-unsaved-bar action="submit" />
            <x-application.settings-section :title="__('common.webhook')"
                :description="__('notifications.webhook_description')">
                <x-slot:actions>
                    <x-notification.channel-actions :enabled="$webhookEnabled" enabledProperty="webhookEnabled"
                        toggleMethod="instantSaveWebhookEnabled" :canUpdate="auth()->user()->can('update', $settings)" />
                </x-slot:actions>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        @can('update', $settings)
                            <x-forms.input type="password" required id="webhookUrl"
                                :label="__('notifications.webhook_url')"
                                :helper="__('notifications.webhook_helper')" />
                        @else
                            <x-forms.input disabled :label="__('notifications.webhook_url')"
                                :value="__('common.hidden_admins_only')" />
                        @endcan
                    </div>
                </div>
            </x-application.settings-section>
        </form>

        <x-notification.event-grid :settings="$settings" channel="webhook" />
    </div>
    </x-notification.settings-layout>
</div>
