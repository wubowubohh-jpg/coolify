<x-forms.button wire:click="backupNow"
    :disabled="! str($backup->database->status)->startsWith('running')"
    :tooltip="! str($backup->database->status)->startsWith('running') ? __('common.database_must_running_backup') : null">{{ __('common.back_up_now') }}</x-forms.button>
