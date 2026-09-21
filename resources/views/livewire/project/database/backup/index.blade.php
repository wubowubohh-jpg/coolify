<div>
    <x-slot:title>
        {{ data_get_str($database, 'name')->limit(10) }} > {{ __('common.backups') }} | Coolify
    </x-slot>

    <livewire:project.database.heading :database="$database" />

    <div class="application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-database.configuration-sidebar :database="$database" current-route="project.database.backup.index" />
        <div class="application-settings-form flex min-w-0 flex-col gap-6">
            <x-application.settings-section :title="__('common.database_backups')"
                :description="__('common.database_backups_description')">
                @can('update', $database)
                    <x-slot:actions>
                        <x-modal-input :title="__('common.new_scheduled_backup')" isHighlightedButton :buttonTitle="__('common.add')">
                            <livewire:project.database.create-scheduled-backup :database="$database" />
                        </x-modal-input>
                    </x-slot:actions>
                @endcan

                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">{{ __('common.schedules') }}</p>
                        <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                            {{ $database->scheduledBackups->count() }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">{{ __('common.enabled_label') }}</p>
                        <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                            {{ $database->scheduledBackups->where('enabled', true)->count() }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-neutral-500 dark:text-fg-dim">{{ __('common.total_executions') }}</p>
                        <p class="mt-1 text-xl font-semibold tabular-nums text-neutral-950 dark:text-fg">
                            {{ $database->scheduledBackups->sum('executions_count') }}
                        </p>
                    </div>
                </div>
            </x-application.settings-section>

            <livewire:project.database.scheduled-backups :database="$database" />
        </div>
    </div>
</div>
