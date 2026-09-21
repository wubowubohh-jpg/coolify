<div x-data="{
    error: $wire.entangle('error'),
    filesize: $wire.entangle('filesize'),
    filename: $wire.entangle('filename'),
    isUploading: $wire.entangle('isUploading'),
    progress: $wire.entangle('progress'),
    s3FileSize: $wire.entangle('s3FileSize'),
    s3StorageId: $wire.entangle('s3StorageId'),
    s3Path: $wire.entangle('s3Path'),
    restoreType: null
}">
    <script type="text/javascript" src="{{ URL::asset('js/dropzone.js') }}"></script>
    @script
    <script data-navigate-once>
        Dropzone.options.myDropzone = {
            chunking: true,
            method: "POST",
            maxFilesize: 1000000000,
            chunkSize: 10000000,
            createImageThumbnails: false,
            disablePreviews: true,
            parallelChunkUploads: false,
            init: function () {
                let button = this.element.querySelector('button');
                button.innerText = @js(__('common.select_or_drop_backup'))
                this.on('sending', function (file, xhr, formData) {
                    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    formData.append("_token", token);
                });
                this.on("addedfile", file => {
                    $wire.isUploading = true;
                    $wire.customLocation = '';
                });
                this.on('uploadprogress', function (file, progress, bytesSent) {
                    $wire.progress = progress;
                });
                this.on('complete', function (file) {
                    $wire.filename = file.name;
                    $wire.filesize = Number(file.size / 1024 / 1024).toFixed(2) + ' MB';
                    $wire.isUploading = false;
                });
                this.on('error', function (file, message) {
                    $wire.error = true;
                    $wire.$dispatch('error', message.error)
                });
            }
        };
    </script>
    @endscript
        <div class="application-settings-workspace flex flex-col gap-6">
            <x-callout type="danger" :title="__('common.existing_data_replaced')">
                {{ __('common.restore_backup_destructive') }}
            </x-callout>

            <x-application.settings-section :title="__('common.restore_configuration')"
                :description="__('common.restore_configuration_description')">
                <div class="space-y-4">
            @if ($resourceDbType === 'standalone-postgresql')
                @if ($dumpAll)
                            <x-callout type="warning" :title="__('common.full_restore_overwrites_admin_passwords')">
                                {{ __('common.backup_replaces_postgres_passwords') }}
                                <span class="mt-1 block">{{ __('common.administrator_password_change_update_config') }}</span>
                            </x-callout>
                            <x-forms.textarea rows="6" readonly :label="__('common.import_command')"
                                wire:model="restoreCommandText" canGate="update"
                                :canResource="$this->resource" />
                @else
                            <x-forms.input :label="__('common.import_command')"
                                :helper="__('common.import_command_helper')"
                                wire:model="postgresqlRestoreCommand" canGate="update"
                                :canResource="$this->resource" />
                @endif
            @elseif ($resourceDbType === 'standalone-mysql')
                @if ($dumpAll)
                            <x-forms.textarea rows="10" readonly :label="__('common.import_command')"
                                wire:model="restoreCommandText" canGate="update"
                                :canResource="$this->resource" />
                @else
                            <x-forms.input :label="__('common.import_command')" wire:model="mysqlRestoreCommand"
                                canGate="update" :canResource="$this->resource" />
                @endif
            @elseif ($resourceDbType === 'standalone-mariadb')
                @if ($dumpAll)
                            <x-forms.textarea rows="10" readonly :label="__('common.import_command')"
                                wire:model="restoreCommandText" canGate="update"
                                :canResource="$this->resource" />
                @else
                            <x-forms.input :label="__('common.import_command')" wire:model="mariadbRestoreCommand"
                                canGate="update" :canResource="$this->resource" />
                @endif
            @endif
                    <div class="max-w-sm">
                        <x-forms.listbox id="dumpAll" :label="__('common.backup_contents')" live :options="[
                            ['value' => true, 'label' => __('common.backup_contains_all_databases')],
                            ['value' => false, 'label' => __('common.backup_contains_one_database')],
                        ]" />
                    </div>
                </div>
            </x-application.settings-section>

            <x-application.settings-section :title="__('common.backup_source')"
                :description="__('common.backup_source_description')">
                <div class="grid gap-3 sm:grid-cols-2">
                    <button type="button" @click="restoreType = 'file'"
                        class="flex min-h-20 items-center gap-3 rounded-[10px] border p-3 text-left transition-colors"
                        :class="restoreType === 'file'
                            ? 'border-coollabs/35 bg-coollabs/[0.06] text-coollabs dark:border-warning/30 dark:bg-warning/[0.08] dark:text-warning'
                            : 'border-neutral-200 bg-white hover:border-neutral-300 dark:border-white/[0.08] dark:bg-white/[0.05] dark:hover:border-white/[0.14]'">
                        <span
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 dark:bg-white/[0.06]">
                            <x-reicon name="file" class="size-4" />
                        </span>
                        <span>
                            <span class="block text-[13px] font-semibold">{{ __('common.file') }}</span>
                            <span class="mt-0.5 block text-[11px] text-neutral-500 dark:text-fg-faint">{{ __('common.upload_or_use_server_path') }}</span>
                        </span>
                    </button>

                @if (count($availableS3Storages) > 0)
                        <button type="button" @click="restoreType = 's3'"
                            class="flex min-h-20 items-center gap-3 rounded-[10px] border p-3 text-left transition-colors"
                            :class="restoreType === 's3'
                                ? 'border-coollabs/35 bg-coollabs/[0.06] text-coollabs dark:border-warning/30 dark:bg-warning/[0.08] dark:text-warning'
                                : 'border-neutral-200 bg-white hover:border-neutral-300 dark:border-white/[0.08] dark:bg-white/[0.05] dark:hover:border-white/[0.14]'">
                            <span
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 dark:bg-white/[0.06]">
                                <x-reicon name="storages" class="size-4" />
                            </span>
                            <span>
                                <span class="block text-[13px] font-semibold">{{ __('common.s3_storage') }}</span>
                                <span class="mt-0.5 block text-[11px] text-neutral-500 dark:text-fg-faint">{{ __('common.download_backup_s3') }}</span>
                            </span>
                        </button>
                @endif
                </div>

            {{-- File Restore Section --}}
            @can('update', $this->resource)
                <div x-cloak x-show="restoreType === 'file'"
                    class="mt-4 rounded-[10px] border border-neutral-200 bg-neutral-50 p-4 dark:border-white/[0.08] dark:bg-white/[0.05]">
                    <form class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="min-w-0 flex-1">
                            <x-forms.input :label="__('common.file_path_on_server')"
                                placeholder="/home/user/backup.sql.gz" wire:model="customLocation"
                                x-model="$wire.customLocation" canGate="update"
                                :canResource="$this->resource" />
                        </div>
                        <x-forms.button wire:click="checkFile" x-bind:disabled="!$wire.customLocation"
                            canGate="update" :canResource="$this->resource">{{ __('common.check_file') }}</x-forms.button>
                    </form>

                    <div class="my-4 flex items-center gap-3 text-[10px] font-medium uppercase tracking-wide text-neutral-400 dark:text-fg-faint">
                        <span class="h-px flex-1 bg-neutral-200 dark:bg-white/[0.08]"></span>
                        {{ __('common.or_upload') }}
                        <span class="h-px flex-1 bg-neutral-200 dark:bg-white/[0.08]"></span>
                    </div>

                    <form action="{{ route('upload.backup', ['databaseUuid' => $resourceUuid]) }}"
                        class="dropzone rounded-lg! border! border-dashed! border-neutral-300! bg-white! dark:border-white/[0.12]! dark:bg-white/[0.05]!"
                        id="my-dropzone" wire:ignore>
                        @csrf
                    </form>
                    <div x-show="isUploading" class="mt-3 h-1.5 overflow-hidden rounded-full bg-neutral-200 dark:bg-white/[0.08]">
                        <div class="h-full rounded-full bg-coollabs dark:bg-warning"
                            :style="`width: ${progress}%`"></div>
                    </div>

                    <div x-cloak x-show="filename && !error"
                        class="mt-4 flex flex-col gap-3 rounded-lg border border-neutral-200 bg-white p-3 dark:border-white/[0.08] dark:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-[12px] font-medium" x-text="filename ?? 'N/A'"></p>
                            <p class="mt-0.5 text-[11px] text-neutral-500 dark:text-fg-faint"
                                x-show="filesize" x-text="filesize"></p>
                        </div>
                        <div class="shrink-0">
                            <x-modal-confirmation :title="__('common.restore_database_from_file')" :buttonTitle="__('common.restore_from_file')"
                                submitAction="runImport" isErrorButton>
                                <x-slot:button-title>
                                    {{ __('common.restore_from_file') }}
                                </x-slot:button-title>
                                {{ __('common.this_will') }}
                                <ul class="list-disc list-inside pt-2">
                                    <li>{{ __('common.copy_backup_to_database_container') }}</li>
                                    <li>{{ __('common.execute_restore_command') }}</li>
                                </ul>
                                <p class="pt-2 font-semibold text-error">{{ __('common.all_existing_data_replaced') }}</p>
                            </x-modal-confirmation>
                        </div>
                    </div>
                </div>
            @endcan

            {{-- S3 Restore Section --}}
            @if (count($availableS3Storages) > 0)
                @can('update', $this->resource)
                    @php
                        $s3StorageOptions = collect($availableS3Storages)
                            ->map(fn ($storage) => [
                                'value' => $storage['id'],
                                'label' => $storage['name']
                                    .($storage['description'] ? ' - '.$storage['description'] : ''),
                            ])
                            ->values()
                            ->all();
                    @endphp
                    <div x-cloak x-show="restoreType === 's3'"
                        class="mt-4 rounded-[10px] border border-neutral-200 bg-neutral-50 p-4 dark:border-white/[0.08] dark:bg-white/[0.05]">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.listbox id="s3StorageId" :label="__('common.s3_storage')" :options="$s3StorageOptions"
                                :placeholder="__('common.select_storage')" live />

                            <x-forms.input :label="__('common.file_path')"
                                :helper="__('common.s3_backup_path_helper')"
                                placeholder="/backups/database-backup.gz" wire:model.blur="s3Path"
                                wire:keydown.enter="checkS3File" canGate="update"
                                :canResource="$this->resource" />
                        </div>

                        <div class="mt-3 flex justify-end">
                            <x-forms.button wire:click="checkS3File" x-bind:disabled="!s3StorageId || !s3Path"
                                canGate="update" :canResource="$this->resource">
                                {{ __('common.check_file') }}
                            </x-forms.button>
                        </div>

                            @if ($s3FileSize)
                            <div
                                class="mt-4 flex flex-col gap-3 rounded-lg border border-neutral-200 bg-white p-3 dark:border-white/[0.08] dark:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate text-[12px] font-medium">{{ $s3Path }}</p>
                                    <p class="mt-0.5 text-[11px] text-neutral-500 dark:text-fg-faint">
                                        {{ formatBytes($s3FileSize ?? 0) }}</p>
                                </div>
                                <div class="shrink-0">
                                        <x-modal-confirmation :title="__('common.restore_database_from_s3')" :buttonTitle="__('common.restore_from_s3')"
                                            submitAction="restoreFromS3" isErrorButton>
                                            <x-slot:button-title>
                                            {{ __('common.restore_from_s3') }}
                                            </x-slot:button-title>
                                        {{ __('common.this_will') }}
                                            <ul class="list-disc list-inside pt-2">
                                                <li>{{ __('common.download_backup_from_s3') }}</li>
                                                <li>{{ __('common.copy_file_into_database_container') }}</li>
                                                <li>{{ __('common.execute_restore_command') }}</li>
                                            </ul>
                                        <p class="pt-2 font-semibold text-error">{{ __('common.all_existing_data_replaced') }}</p>
                                        </x-modal-confirmation>
                                </div>
                            </div>
                            @endif
                    </div>
                @endcan
            @endif
            </x-application.settings-section>
        </div>

            <x-process-dialog @databaserestore.window="processDialogOpen = true" closeWithX size="xl">
                <x-slot:title>{{ __('common.database_restore_output') }}</x-slot:title>
                <x-slot:content>
                    <div class="flex h-full min-h-0 flex-col" wire:ignore>
                        <livewire:activity-monitor wire:key="database-restore-{{ $resourceUuid }}" :header="__('common.logs')" fullHeight />
                    </div>
                </x-slot:content>
            </x-process-dialog>
</div>
