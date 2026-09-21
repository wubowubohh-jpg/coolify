<div class="application-settings-form">
    <x-application.settings-section :title="__('common.delete_backup_schedule')"
        :description="__('common.delete_backup_schedule_description')">
        <x-danger-zone :title="__('common.action_cannot_be_undone').'.'">
            <p>{{ __('common.select_backup_archives') }}</p>
            <x-slot:action>
        @if ($backup->database_id !== 0)
            <x-modal-confirmation :title="__('common.confirm_backup_schedule_deletion')" isErrorButton submitAction="delete"
                :checkboxes="$checkboxes" :actions="[
                    __('common.selected_backup_schedule_deleted'),
                    __('common.scheduled_backups_stop'),
                ]"
                confirmationText="{{ $backup->database->name }}"
                :confirmationLabel="__('common.enter_database_name_confirm')"
                :shortConfirmationLabel="__('common.database_name')">
                <x-slot:trigger>
                    <x-forms.button isError>{{ __('common.delete_schedule') }}</x-forms.button>
                </x-slot:trigger>
            </x-modal-confirmation>
        @endif
            </x-slot:action>
        </x-danger-zone>
    </x-application.settings-section>
</div>
