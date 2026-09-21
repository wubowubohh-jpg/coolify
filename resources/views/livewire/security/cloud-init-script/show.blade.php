<div>
    @if ($modalMode)
        <form wire:submit="save" class="flex flex-col gap-4">
            <x-forms.input canGate="update" :canResource="$cloudInitScript" id="name" :label="__('common.script_name')" required />
            <x-forms.textarea canGate="update" :canResource="$cloudInitScript" id="script"
                :label="__('common.script_content')" rows="16" monospace
                :helper="__('common.cloud_config_helper')" required />
            <div class="flex items-center justify-between gap-2 border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                @can('delete', $cloudInitScript)
                    <x-modal-confirmation :title="__('common.confirm_script_deletion')" isErrorButton :buttonTitle="__('common.delete')"
                        submitAction="delete" :actions="[__('common.cloud_init_delete_action')]"
                        confirmationText="{{ $cloudInitScript->name }}" :confirmWithPassword="false"
                        :step2ButtonText="__('common.delete_script')" />
                @endcan
                <x-forms.button type="submit" isHighlighted>{{ __('common.save_changes') }}</x-forms.button>
            </div>
        </form>
    @else
    <x-slot:title>
        {{ $cloudInitScript->name }} | {{ __('common.cloud_init_scripts') }} | Coolify
    </x-slot>

    <x-security.settings-layout>
        <x-slot:actions>
            @can('delete', $cloudInitScript)
                <x-modal-confirmation :title="__('common.confirm_script_deletion')" isErrorButton :buttonTitle="__('common.delete')"
                    submitAction="delete" :actions="[
                        __('common.cloud_init_delete_action'),
                        __('common.permanent_warning'),
                    ]" confirmationText="{{ $cloudInitScript->name }}"
                    :confirmationLabel="__('common.enter_script_name')"
                    :shortConfirmationLabel="__('common.script_name')" :confirmWithPassword="false"
                    :step2ButtonText="__('common.delete_script')" />
            @endcan
        </x-slot:actions>


    <form wire:submit="save" class="application-settings-form">
        <x-unsaved-bar action="save" />
        <x-application.settings-section :title="__('common.general')"
            :description="__('common.cloud_init_general_description')">
            <div class="flex flex-col gap-4">
                <x-forms.input canGate="update" :canResource="$cloudInitScript" id="name"
                    :label="__('common.script_name')" required />
                <x-forms.textarea canGate="update" :canResource="$cloudInitScript" id="script"
                    :label="__('common.script_content')" rows="18" monospace
                    :helper="__('common.cloud_config_helper')" required />
            </div>
        </x-application.settings-section>
    </form>
    </x-security.settings-layout>
    @endif
</div>
