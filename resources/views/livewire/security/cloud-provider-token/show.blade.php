<div>
    @if ($modalMode)
        <form wire:submit="save" class="flex flex-col gap-4">
            <div class="grid gap-4 lg:grid-cols-2">
                <x-forms.input canGate="update" :canResource="$cloudProviderToken" id="name" :label="__('common.name')" required />
                <x-forms.input canGate="update" :canResource="$cloudProviderToken" id="description" :label="__('common.description')" />
                <x-forms.input readonly :label="__('common.provider')" :value="$this->providerName()" />
                <x-forms.input readonly :label="__('common.created')" :value="$cloudProviderToken->created_at->format('Y-m-d H:i')" />
            </div>
            <div class="flex items-center justify-between gap-2 border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                <div class="flex items-center gap-2">
                    @can('delete', $cloudProviderToken)
                        <x-modal-confirmation :title="__('common.confirm_token_deletion')" isErrorButton :buttonTitle="__('common.delete')"
                            submitAction="delete" :actions="[__('common.cloud_provider_token_delete_action')]"
                            confirmationText="{{ $cloudProviderToken->name }}" :confirmWithPassword="false"
                            :step2ButtonText="__('common.delete_token')" />
                    @endcan
                    <x-forms.button type="button" wire:click="validateToken">{{ __('common.validate') }}</x-forms.button>
                </div>
                <x-forms.button type="submit" isHighlighted>{{ __('common.save_changes') }}</x-forms.button>
            </div>
        </form>
    @else
    <x-slot:title>
        {{ $cloudProviderToken->name }} | {{ __('common.cloud_tokens') }} | Coolify
    </x-slot>

    <x-security.settings-layout>
        <x-slot:actions>
            <x-forms.button type="button" wire:click="validateToken">
                <x-reicon name="check-circle" class="size-3.5" />
                {{ __('common.validate') }}
            </x-forms.button>
            @can('delete', $cloudProviderToken)
                <x-modal-confirmation :title="__('common.confirm_token_deletion')" isErrorButton :buttonTitle="__('common.delete')"
                    submitAction="delete" :actions="[
                        __('common.cloud_provider_token_delete_action'),
                        __('common.servers_using_token_warning'),
                    ]" confirmationText="{{ $cloudProviderToken->name }}"
                    :confirmationLabel="__('common.enter_token_name')"
                    :shortConfirmationLabel="__('common.token_name')" :confirmWithPassword="false"
                    :step2ButtonText="__('common.delete_token')" />
            @endcan
        </x-slot:actions>


    <form wire:submit="save" class="application-settings-form">
        <x-unsaved-bar action="save" />
        <x-application.settings-section :title="__('common.general')"
            :description="__('common.cloud_token_general_description')">
            <div class="grid gap-4 lg:grid-cols-2">
                <x-forms.input canGate="update" :canResource="$cloudProviderToken" id="name"
                    :label="__('common.name')" required />
                <x-forms.input canGate="update" :canResource="$cloudProviderToken" id="description"
                    :label="__('common.description')" />
                <x-forms.input readonly :label="__('common.provider')" :value="$this->providerName()" />
                <x-forms.input readonly :label="__('common.created')" :value="$cloudProviderToken->created_at->format('Y-m-d H:i')" />
            </div>
        </x-application.settings-section>
    </form>
    </x-security.settings-layout>
    @endif
</div>
