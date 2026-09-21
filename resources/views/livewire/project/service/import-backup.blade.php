<div>
    <x-slot:title>
        {{ data_get_str($service, 'name')->limit(10) }} > Import Backup | Coolify
    </x-slot>

    <livewire:project.service.heading :service="$service" :parameters="$parameters" :query="request()->query()"
        wire:key="service-heading-import-backup" />

    <section class="application-settings-workspace mt-4 w-full max-w-none lg:mt-0">
        <div class="grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
            <x-service.configuration-sidebar :service="$service" current-route="project.service.import-backup" />

            <div class="application-settings-form min-w-0 flex flex-col gap-6">
                @if ($databases->isEmpty())
                    <x-application.settings-section :title="__('common.import_backup')"
                        :helper="__('common.import_backup_description')">
                        <x-empty :title="__('common.no_compatible_databases')"
                            :description="__('common.service_no_backup_database')"
                            icon-name="database" size="sm" />
                    </x-application.settings-section>
                @else
                    <x-application.settings-section :title="__('common.import_backup')"
                        :helper="__('common.choose_backup_database')">
                        <x-forms.listbox id="selectedDatabaseUuid" :label="__('common.database')" live required canGate="update"
                            :canResource="$service"
                            :options="$databases->map(fn ($database) => [
                                'value' => $database->uuid,
                                'label' => $database->human_name ?: $database->name,
                            ])->all()" />
                    </x-application.settings-section>

                    @if ($selectedDatabase)
                        <livewire:project.database.import :key="'service-import-' . $selectedDatabase->uuid" />
                    @endif
                @endif
            </div>
        </div>
    </section>
</div>
