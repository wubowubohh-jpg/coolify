<div class="application-settings-form">
    @if ($unsupported)
        <x-application.settings-section :title="__('common.restore_database')"
            :description="__('common.restore_database_description')">
            <x-empty :title="__('common.restore_not_supported')"
                :description="__('common.database_backup_import_not_supported')"
                icon-name="database" size="sm" />
        </x-application.settings-section>
    @elseif (str($resourceStatus)->startsWith('running'))
        <livewire:project.database.import-form wire:key="database-import-form-{{ $resourceUuid }}" />
    @else
        <x-application.settings-section :title="__('common.restore_database')"
            :description="__('common.restore_database_description')">
            <x-empty :title="__('common.start_database_first')"
                :description="__('common.database_running_before_restore')"
                icon-name="database" size="sm" />
        </x-application.settings-section>
    @endif
</div>
