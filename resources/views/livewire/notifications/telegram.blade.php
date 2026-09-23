<div>
    <x-slot:title>
        {{ __('common.notifications') }} - {{ __('common.telegram') }} | Coolify
    </x-slot>

    <x-notification.settings-layout>
    <div class="application-settings-form flex flex-col gap-6">
        <form wire:submit="submit">
            <x-unsaved-bar action="submit" />
            <x-application.settings-section :title="__('common.telegram')"
                :description="__('notifications.telegram_description')">
                <x-slot:actions>
                    <x-notification.channel-actions :enabled="$telegramEnabled" enabledProperty="telegramEnabled"
                        toggleMethod="instantSaveTelegramEnabled" :canUpdate="auth()->user()->can('update', $settings)" />
                </x-slot:actions>

                <div class="grid gap-4 lg:grid-cols-2">
                    @can('update', $settings)
                        <x-forms.input type="password" autocomplete="new-password" required id="telegramToken"
                            :label="__('notifications.bot_api_token')"
                            :helper="__('notifications.bot_api_token_helper')" />
                        <x-forms.input type="password" autocomplete="new-password" required id="telegramChatId"
                            :label="__('notifications.chat_id')"
                            :helper="__('notifications.chat_id_helper')" />
                    @else
                        <x-forms.input disabled :label="__('notifications.bot_api_token')"
                            :value="__('common.hidden_admins_only')" />
                        <x-forms.input disabled :label="__('notifications.chat_id')"
                            :value="__('common.hidden_admins_only')" />
                    @endcan
                </div>
            </x-application.settings-section>
        </form>

        <x-notification.event-grid :settings="$settings" channel="telegram" threaded />
    </div>
    </x-notification.settings-layout>
</div>
