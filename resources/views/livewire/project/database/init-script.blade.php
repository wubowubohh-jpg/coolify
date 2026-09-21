<form wire:submit="submit" class="application-settings-form flex flex-col gap-4 px-4 py-4">
    <x-unsaved-bar action="submit" />
    <div class="flex items-end gap-2">
        <x-forms.input id="filename" :label="__('common.filename')" />
        <x-modal-confirmation :title="__('common.delete_initialization_script')" :buttonTitle="__('common.delete')" isErrorButton
            submitAction="delete" :actions="[
                __('common.initialization_script_delete_actions'),
                __('common.redeployments_may_fail'),
            ]" confirmationText="{{ $filename }}"
            :confirmationLabel="__('common.enter_initialization_script_name')"
            :shortConfirmationLabel="__('common.script_name')" :confirmWithPassword="false"
            :step2ButtonText="__('common.permanently_delete_initialization')" />
    </div>
    <x-forms.textarea id="content" :label="__('common.content')" rows="12" />
</form>
