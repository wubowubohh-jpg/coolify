<div wire:init="refreshBackupExecutions">
    @isset($backup)
        <section class="application-settings-section overflow-hidden">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.executions') }}</h2>
                    <p>{{ __('common.backup_executions_helper') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <x-forms.button wire:click="cleanupFailed">{{ __('common.clean_failed_backups') }}</x-forms.button>
                    <x-modal-confirmation :title="__('common.cleanup_deleted_backup_entries')" isErrorButton
                        submitAction="cleanupDeleted()" :actions="[
                            __('common.delete_removed_execution_records'),
                            __('common.actual_backup_files_not_changed'),
                        ]"
                        :confirmationText="__('common.cleanup_deleted_backups')"
                        :confirmationLabel="__('common.type_cleanup_deleted_confirm')"
                        :shortConfirmationLabel="__('common.confirmation')">
                        <x-slot:trigger>
                            <x-forms.button isError>{{ __('common.clean_deleted_entries') }}</x-forms.button>
                        </x-slot:trigger>
                    </x-modal-confirmation>
                </div>
            </div>

            <div @if (! $skip) wire:poll.5000ms="refreshBackupExecutions" @endif
                class="application-settings-section-body p-0!">
                @if ($executions_count === 0)
                    <div class="p-4">
                        <x-empty size="sm" :title="__('common.no_backup_executions')"
                            :description="__('common.execution_history_after_backup')"
                            icon-name="browser-terminal" />
                    </div>
                @else
                    <div class="data-table deployment-table-scroll backup-executions-table-scroll">
                        <div
                            class="data-table-header backup-executions-table-grid h-auto rounded-none px-4 py-2.5 text-[11px]">
                            <span>{{ __('common.status') }}</span>
                            <span>{{ __('common.database_column') }}</span>
                            <span>{{ __('common.backup_path') }}</span>
                            <span>{{ __('common.finished') }}</span>
                            <span>{{ __('common.duration') }}</span>
                            <span>{{ __('common.size') }}</span>
                            <span>{{ __('common.availability') }}</span>
                            <span class="text-right">{{ __('common.actions') }}</span>
                        </div>
                        @foreach ($executions as $execution)
                            @php
                                $executionStatus = data_get($execution, 'status');
                                [$executionStatusLabel, $executionStatusType] = match ($executionStatus) {
                                    'success' => data_get($execution, 's3_uploaded') === false
                                        ? [__('common.s3_warning'), 'warning']
                                        : [__('common.success'), 'success'],
                                    'running' => [__('common.in_progress'), 'warning'],
                                    'failed' => [__('common.failed'), 'error'],
                                    default => [str($executionStatus)->headline(), 'neutral'],
                                };
                                $executionCheckboxes = [];
                                $deleteActions = [];

                                if (! data_get($execution, 'local_storage_deleted', false)) {
                                    $deleteActions[] = __('common.backup_deleted_local_storage');
                                }

                                if (data_get($execution, 's3_uploaded') === true
                                    && ! data_get($execution, 's3_storage_deleted', false)) {
                                    $executionCheckboxes[] = [
                                        'id' => 'delete_backup_s3',
                                        'label' => __('common.delete_selected_backup_s3'),
                                    ];
                                }

                                if (empty($deleteActions)) {
                                    $deleteActions[] = __('common.backup_execution_record_deleted');
                                }
                            @endphp
                            <div wire:key="{{ data_get($execution, 'id') }}"
                                class="backup-execution-row">
                                <div class="data-table-row backup-executions-table-grid min-h-14 border-b border-neutral-200 px-4 py-2.5 dark:border-white/[0.06]">
                                    <div class="flex items-center gap-2">
                                        <x-status-badge :status="$executionStatusLabel"
                                            :type="$executionStatusType" />
                                        @if ($executionStatus === 'running')
                                            <x-loading />
                                        @endif
                                    </div>
                                    <div class="truncate text-[12px] font-medium text-black dark:text-fg">
                                        {{ data_get($execution, 'database_name', 'N/A') }}
                                    </div>
                                    <div class="flex min-w-0 items-center gap-1">
                                        <code class="select-all truncate font-mono text-[11px] text-neutral-600 dark:text-fg-dim"
                                            :title="__('common.backup_path').': '.data_get($execution, 'filename', 'N/A')">{{ data_get($execution, 'filename', 'N/A') }}</code>
                                        <x-copy-button :value="data_get($execution, 'filename', '')" :label="__('common.copy_backup_path')" />
                                    </div>
                                    <div class="text-[11px] text-neutral-600 dark:text-fg-dim">
                                        @if ($executionStatus === 'running')
                                            {{ __('common.running_now') }}
                                        @else
                                            {{ \Carbon\Carbon::parse(data_get($execution, 'finished_at'))->diffForHumans() }}
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-neutral-600 dark:text-fg-dim">
                                        {{ calculateDuration(
                                            data_get($execution, 'created_at'),
                                            $executionStatus === 'running' ? now() : data_get($execution, 'finished_at'),
                                        ) }}
                                    </div>
                                    <div class="text-[11px] text-neutral-600 dark:text-fg-dim">
                                        {{ data_get($execution, 'size') ? formatBytes(data_get($execution, 'size')) : '-' }}
                                    </div>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <x-status-badge :label="__('common.local')"
                                            :status="data_get($execution, 'local_storage_deleted', false) ? __('common.deleted') : __('common.available')"
                                            :type="data_get($execution, 'local_storage_deleted', false) ? 'neutral' : 'success'" />
                                        @if (data_get($execution, 's3_uploaded') !== null)
                                            <x-status-badge :label="__('common.s3')"
                                                :status="data_get($execution, 's3_storage_deleted', false)
                                                    ? __('common.deleted')
                                                    : (data_get($execution, 's3_uploaded') ? __('common.available') : __('common.failed'))"
                                                :type="data_get($execution, 's3_storage_deleted', false)
                                                    ? 'neutral'
                                                    : (data_get($execution, 's3_uploaded') ? 'success' : 'error')" />
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-end gap-1">
                                        @if ($executionStatus === 'success')
                                            <button type="button" class="icon-button shrink-0"
                                                x-on:click="download_file('{{ data_get($execution, 'id') }}')"
                                                :title="__('common.download_backup')" :aria-label="__('common.download_backup')">
                                                <x-reicon name="upload" class="size-3.5 rotate-180" />
                                            </button>
                                        @endif
                                        <x-modal-confirmation :title="__('common.confirm_backup_deletion')" isErrorButton
                                            submitAction="deleteBackup({{ data_get($execution, 'id') }})"
                                            :checkboxes="$executionCheckboxes" :actions="$deleteActions"
                                            confirmationText="{{ data_get($execution, 'filename') }}"
                                        :confirmationLabel="__('common.enter_backup_filename_confirm')"
                                        :shortConfirmationLabel="__('common.backup_filename')">
                                            <x-slot:trigger>
                                                <button type="button"
                                                    class="icon-button shrink-0 text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
                                                    :title="__('common.delete_backup')" :aria-label="__('common.delete_backup')">
                                                    <x-reicon name="trash" class="size-3.5" />
                                                </button>
                                            </x-slot:trigger>
                                        </x-modal-confirmation>
                                    </div>
                                </div>
                                @if (data_get($execution, 'message'))
                                    <div class="border-t border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-white/[0.06] dark:bg-white/[0.02]">
                                        <pre
                                            class="max-h-48 overflow-auto whitespace-pre-wrap rounded-lg bg-neutral-100 p-3 font-mono text-xs leading-5 text-neutral-700 dark:bg-black/20 dark:text-fg-dim">{{ data_get($execution, 'message') }}</pre>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        </div>

                    <div
                        class="flex min-h-11 items-center justify-between border-t border-neutral-200 px-4 text-[11px] text-neutral-500 dark:border-white/[0.08] dark:text-fg-faint">
                        <span>
                            {{ $skip + 1 }}-{{ min($skip + $defaultTake, $executions_count) }} of
                            {{ $executions_count }}
                        </span>
                        <div class="flex items-center gap-1">
                            <button type="button" class="icon-button" @disabled(! $showPrev)
                                wire:click="previousPage('{{ $defaultTake }}')" :aria-label="__('common.previous_page')">
                                <x-reicon name="arrow-right" class="size-3.5 rotate-180" />
                            </button>
                            <button type="button" class="icon-button" @disabled(! $showNext)
                                wire:click="nextPage('{{ $defaultTake }}')" :aria-label="__('common.next_page')">
                                <x-reicon name="arrow-right" class="size-3.5" />
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endisset
</div>

@script
    <script>
        window.download_file = function(executionId) {
            window.open('/download/backup/' + executionId, '_blank');
        }
    </script>
@endscript
