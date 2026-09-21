<div>
    <x-slot:title>
        {{ __('settings.transactional_email') }} | Coolify
    </x-slot>

    <x-settings.layout>
    <div class="application-settings-form mx-auto flex w-full max-w-none min-w-0 flex-col gap-6">
        {{-- One bar for the whole page. Three stacked bars made Save run
             submitResend(), which required an API key even when Resend was off. --}}
        <x-unsaved-bar action="submit"
            targets="smtpFromName,smtpFromAddress,smtpHost,smtpPort,smtpEncryption,smtpUsername,smtpPassword,smtpTimeout,smtpEhloDomain,resendApiKey" />

        <form wire:submit="submit">
            <x-application.settings-section :title="__('settings.sender')">
                <x-slot:actions>
                    @include('livewire.partials.settings-email-send-test')
                </x-slot:actions>
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.input required id="smtpFromName" :helper="__('settings.from_name_helper')"
                        :label="__('settings.from_name')" />
                    <x-forms.input required id="smtpFromAddress" :helper="__('settings.from_address_helper')"
                        :label="__('settings.from_address')" />
                </div>
            </x-application.settings-section>
        </form>

        <form wire:submit.prevent="submitSmtp">
            <x-application.settings-section :title="__('settings.smtp_server')">
                <div class="grid gap-4 lg:grid-cols-3">
                    <div class="lg:col-span-3">
                        <div class="w-full sm:w-72">
                            <x-forms.listbox id="smtpEnabled" :label="__('settings.smtp_delivery')"
                                onChange="instantSaveSmtp" :options="[
                                    ['value' => true, 'label' => __('profile.enabled')],
                                    ['value' => false, 'label' => __('common.disabled')],
                                ]" />
                        </div>
                    </div>
                    <x-forms.input required id="smtpHost" placeholder="smtp.mailgun.org" :label="__('settings.host')" />
                    <x-forms.input required id="smtpPort" type="number" placeholder="587" :label="__('settings.port')" />
                    <x-forms.listbox required id="smtpEncryption" :label="__('settings.encryption')" :options="[
                        ['value' => 'starttls', 'label' => __('settings.starttls')],
                        ['value' => 'tls', 'label' => __('settings.tls_ssl')],
                        ['value' => 'none', 'label' => __('settings.none')],
                    ]" />
                    <x-forms.input id="smtpUsername" :label="__('settings.username')" />
                    <x-forms.input id="smtpPassword" type="password" :label="__('settings.password')"
                        autocomplete="new-password" />
                    <x-forms.input id="smtpTimeout" type="number"
                        :helper="__('settings.timeout_helper')" :label="__('settings.timeout')" />
                    <x-forms.input id="smtpEhloDomain" placeholder="coolify.example.com"
                        :helper="__('settings.ehlo_helper')"
                        :label="__('settings.ehlo_domain')" />
                </div>
            </x-application.settings-section>
        </form>

        <form wire:submit.prevent="submitResend">
            <x-application.settings-section :title="__('settings.resend')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.listbox id="resendEnabled" :label="__('settings.resend_delivery')"
                        onChange="instantSaveResend" :options="[
                            ['value' => true, 'label' => __('profile.enabled')],
                            ['value' => false, 'label' => __('common.disabled')],
                        ]" />
                    <x-forms.input type="password" id="resendApiKey" placeholder="API key"
                        :required="$resendEnabled" :label="__('settings.api_key')" autocomplete="new-password" />
                </div>
            </x-application.settings-section>
        </form>
    </div>
    </x-settings.layout>
</div>
