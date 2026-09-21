<form wire:submit="submit">
    <x-unsaved-bar action="submit" />

    <x-application.settings-section :title="__('common.retention')"
        :description="__('common.retention_description')">
        <div class="space-y-6">
            <div>
                <h3 class="mb-3 text-sm font-semibold text-black dark:text-fg">{{ __('common.local_backups') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-forms.input :label="__('common.backups_to_keep')" id="databaseBackupRetentionAmountLocally"
                        type="number" min="0" :helper="__('common.maximum_recent_local_backups')" required />
                    <x-forms.input :label="__('common.days_to_keep')" id="databaseBackupRetentionDaysLocally"
                        type="number" min="0" :helper="__('common.remove_old_local_backups')" required />
                    <x-forms.input :label="__('common.maximum_storage_gb')"
                        id="databaseBackupRetentionMaxStorageLocally" type="number" min="0" step="any"
                        :helper="__('common.remove_old_local_by_size')" required />
                </div>
            </div>

            <div class="border-t border-neutral-200 pt-6 dark:border-white/[0.06]">
                <h3 class="mb-3 text-sm font-semibold text-black dark:text-fg">{{ __('common.s3_backups') }}</h3>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <x-forms.input :label="__('common.backups_to_keep')" id="databaseBackupRetentionAmountS3"
                        type="number" min="0" :helper="__('common.maximum_recent_s3_backups')" required />
                    <x-forms.input :label="__('common.days_to_keep')" id="databaseBackupRetentionDaysS3"
                        type="number" min="0" :helper="__('common.remove_old_s3_backups')" required />
                    <x-forms.input :label="__('common.maximum_storage_gb')" id="databaseBackupRetentionMaxStorageS3"
                        type="number" min="0" step="any"
                        :helper="__('common.remove_old_s3_by_size')" required />
                </div>
            </div>
        </div>
    </x-application.settings-section>
</form>
