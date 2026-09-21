@props([
    'database',
    'label',
    'dbUrl' => null,
    'dbUrlPublic' => null,
    'supportsSsl' => true,
    'enableSsl' => false,
    'sslMode' => null,
    'sslModeOptions' => null,
    'sslModeHelper' => null,
    'certificateValidUntil' => null,
    'isExited' => false,
    'showPublicUrlPlaceholder' => false,
    'isPasswordHiddenForMember' => false,
])

@php
    $urlHelper = __('common.database_url_helper');
@endphp

<div class="space-y-5">
    @if ($isPasswordHiddenForMember)
        <x-forms.input :label="__('common.url_internal', ['label' => $label])" disabled :value="__('common.hidden_admins_only')" />
        <x-forms.input :label="__('common.url_public', ['label' => $label])" disabled :value="__('common.hidden_admins_only')" />
    @else
        <x-forms.input :label="__('common.url_internal', ['label' => $label])" :helper="$urlHelper" type="password" readonly
            wire:model="dbUrl" canGate="update" :canResource="$database" />
        @if ($dbUrlPublic)
            <x-forms.input :label="__('common.url_public', ['label' => $label])" :helper="$urlHelper" type="password" readonly
                wire:model="dbUrlPublic" canGate="update" :canResource="$database" />
        @elseif ($showPublicUrlPlaceholder)
            <x-forms.input :label="__('common.url_public', ['label' => $label])" :helper="$urlHelper" readonly
                :value="__('common.starting_database_generates')" canGate="update" :canResource="$database" />
        @endif
    @endif

    @if ($supportsSsl)
        <div class="border-t border-neutral-200 pt-5 dark:border-white/[0.06]">
            <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-black dark:text-fg">{{ __('common.ssl_configuration') }}</h3>
                        <p class="mt-1 text-xs text-neutral-500 dark:text-fg-dim">
                            {{ __('common.encryption_settings_stopped') }}
                        </p>
                    </div>
                    @if ($enableSsl && $certificateValidUntil)
                        <x-modal-confirmation :title="__('common.regenerate_ssl_certificates')"
                            :buttonTitle="__('common.regenerate_ssl_certificates')" :actions="[
                                __('common.ssl_certificate_regenerated'),
                                __('common.restart_after_certificate'),
                            ]"
                            submitAction="regenerateSslCertificate" :confirmWithText="false" :confirmWithPassword="false" />
                    @endif
            </div>
            @if ($enableSsl && $certificateValidUntil)
                <div class="mb-4 text-sm text-neutral-600 dark:text-fg-dim">{{ __('common.valid_until') }}
                    @if (now()->gt($certificateValidUntil))
                        <span class="text-red-500">{{ $certificateValidUntil->format('d.m.Y H:i:s') }} - {{ __('common.expired') }}</span>
                    @elseif(now()->addDays(30)->gt($certificateValidUntil))
                        <span class="text-red-500">{{ $certificateValidUntil->format('d.m.Y H:i:s') }} - {{ __('common.expiring_soon') }}</span>
                    @else
                        <span>{{ $certificateValidUntil->format('d.m.Y H:i:s') }}</span>
                    @endif
                </div>
            @endif
            <div class="grid gap-4 sm:grid-cols-2">
                <x-forms.listbox canGate="update" :canResource="$database" id="enableSsl" :label="__('common.ssl')"
                    onChange="instantSaveSSL"
                    :disabled="! $isExited || ! auth()->user()?->can('update', $database)"
                    :options="[
                        ['value' => true, 'label' => __('common.enabled')],
                        ['value' => false, 'label' => __('common.disabled')],
                    ]" />
                @if ($sslModeOptions)
                    <x-forms.listbox canGate="update" :canResource="$database" id="sslMode" :label="__('common.ssl_mode')" :helper="$sslModeHelper"
                        onChange="instantSaveSSL"
                        :disabled="! $enableSsl || ! $isExited || ! auth()->user()?->can('update', $database)"
                        :options="collect($sslModeOptions)->map(fn ($option, $value) => [
                            'value' => $value,
                            'label' => $option['label'],
                        ])->values()->all()" />
                @endif
            </div>
        </div>
    @endif
</div>
