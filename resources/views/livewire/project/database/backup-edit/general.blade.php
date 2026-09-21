<form wire:submit="submit">
    <x-unsaved-bar action="submit" />

    <x-application.settings-section :title="__('common.backup_schedule')"
        :description="__('common.backup_schedule_description')">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if (! $backupEnabled)
                    <x-forms.button type="button" wire:click="toggleEnabled" wire:loading.attr="disabled"
                        wire:target="toggleEnabled" isHighlighted>
                        {{ __('common.enable_backup') }}
                    </x-forms.button>
                @else
                    <x-forms.button type="button" wire:click="toggleEnabled" wire:loading.attr="disabled"
                        wire:target="toggleEnabled">
                        {{ __('common.disable_backup') }}
                    </x-forms.button>
                @endif
                <x-forms.button type="button" wire:click="backupNow"
                    :disabled="! str($status)->startsWith('running')"
                    :tooltip="! str($status)->startsWith('running') ? __('common.database_must_running') : null">{{ __('common.back_up_now') }}</x-forms.button>
            </div>
        </x-slot:actions>

        <div class="space-y-5">
            @if ($backup->database_type === 'App\Models\StandalonePostgresql' && $backup->database_id !== 0
                    || $backup->database_type === 'App\Models\StandaloneMysql'
                    || $backup->database_type === 'App\Models\StandaloneMariadb')
                <div class="grid w-full gap-4">
                    <x-forms.listbox id="dumpAll" :label="__('common.database_selection')" onChange="instantSave" :options="[
                        ['value' => true, 'label' => __('common.all_databases')],
                        ['value' => false, 'label' => __('common.specific_databases')],
                    ]" />
                    @if (! $backup->dump_all)
                        <div class="w-full" x-data="{
                            value: @entangle('databasesToBackup').live,
                            draft: '',
                            get databases() {
                                return (this.value || '').split(',').map(name => name.trim()).filter(Boolean);
                            },
                            addDatabase() {
                                const names = this.draft.split(',').map(name => name.trim()).filter(Boolean);
                                if (names.length === 0) return;
                                this.value = [...new Set([...this.databases, ...names])].join(',');
                                this.draft = '';
                            },
                            removeDatabase(index) {
                                this.value = this.databases.filter((_, itemIndex) => itemIndex !== index).join(',');
                            },
                        }">
                            <label class="mb-1.5 block text-sm font-medium">{{ __('common.databases_to_back_up') }}</label>
                            <div class="chip-input">
                                <template x-for="(database, index) in databases" :key="database">
                                    <span class="chip font-mono">
                                        <span x-text="database"></span>
                                        <button type="button" @click="removeDatabase(index)"
                                            class="chip-remove"
                                            :aria-label="`Remove ${database}`">
                                            <x-reicon name="x" class="size-3" />
                                        </button>
                                    </span>
                                </template>
                                <input x-model="draft" @keydown.enter.prevent="addDatabase()"
                                    @keydown="if ($event.key === ',') { $event.preventDefault(); addDatabase(); }"
                                    @blur="addDatabase()" type="text"
                                    placeholder="{{ __('common.type_database_press_enter') }}" />
                            </div>
                            <p class="mt-1.5 text-xs text-neutral-500 dark:text-fg-dim">
                                {{ __('common.add_database_names') }}
                            </p>
                        </div>
                    @endif
                </div>
            @elseif ($backup->database_type === 'App\Models\StandaloneMongodb')
                <x-forms.input :label="__('common.databases_to_include')"
                    :helper="__('common.databases_include_helper')"
                    id="databasesToBackup" />
            @elseif ($backup->database_type === 'App\Models\StandaloneClickhouse')
                <x-forms.input :label="__('common.databases_to_back_up')"
                    :helper="__('common.comma_database_names')"
                    id="databasesToBackup" />
            @endif

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-forms.input :label="__('common.frequency')" id="frequency" required />
                <x-forms.input :label="__('common.timezone')" id="timezone" disabled
                    :helper="__('common.timezone_helper')"
                    required />
                <x-forms.input :label="__('common.timeout')" id="timeout" type="number" min="60"
                    :helper="__('common.max_backup_runtime')" required />
                <x-forms.input :label="__('common.missing_backup_alert_after')" id="missingBackupNotificationDays" type="number"
                    min="0" max="365" :suffix="__('common.days')" canGate="manageBackups" :canResource="$backup->database"
                    :helper="__('common.missing_alert_helper')" required />
            </div>
        </div>
    </x-application.settings-section>
</form>
