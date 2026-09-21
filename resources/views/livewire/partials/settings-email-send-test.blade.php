@php
    $inputId ??= 'testEmailAddress';
@endphp

@if (is_transactional_emails_enabled() && auth()->user()->isAdminFromSession())
    <x-modal-input title="{{ __('settings.send_test_email') }}">
        <x-slot:content>
            <button type="button" class="button">
                <x-reicon name="notifications" class="size-3.5" />
                {{ __('settings.send_test') }}
            </button>
        </x-slot:content>
        <form wire:submit.prevent="sendTestEmail" class="application-settings-form flex flex-col gap-4">
            <x-forms.input wire:model="testEmailAddress" placeholder="test@example.com" :id="$inputId"
                :label="__('settings.recipient')" required />
            <div class="flex justify-end border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                <button type="submit" class="button" @click="modalOpen=false">{{ __('settings.send_email') }}</button>
            </div>
        </form>
    </x-modal-input>
@endif
