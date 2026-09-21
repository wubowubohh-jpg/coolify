<div>
    <div class="flex flex-col gap-4">
        @if ($fileStorage->is_too_large)
            <x-callout type="warning" :title="__('common.file_too_large')">
                {{ __('common.file_too_large_description') }}
            </x-callout>
        @elseif ($fileStorage->is_host_file)
            <x-callout type="info" :title="__('common.host_managed_file')">
                {{ __('common.host_managed_file_description') }}
            </x-callout>
        @elseif ($isReadOnly)
            <x-callout type="info" :title="__('common.read_only_mount')">
                @if ($fileStorage->is_directory)
                    {{ __('common.directory_read_only_description') }}
                @else
                    {{ __('common.file_read_only_description') }}
                @endif
            </x-callout>
        @endif
        <div class="flex flex-col justify-center text-sm select-text">
            <div class="grid gap-4 md:grid-cols-2">
                <x-forms.input :label="__('common.source_path')" :value="$fileStorage->fs_path" readonly>
                    <x-slot:labelSuffix>
                        @if ($hasEnabledBackup)
                            <x-status-badge :as="$backupUrl ? 'a' : 'span'" :href="$backupUrl"
                                :status="__('common.backup_enabled')" type="success"
                                :class="$backupUrl ? 'cursor-pointer underline' : null" />
                        @endif
                    </x-slot:labelSuffix>
                </x-forms.input>
                <x-forms.input :label="__('common.destination_path')" :value="$fileStorage->mount_path" readonly />
            </div>
        </div>
        @if ($resource instanceof \App\Models\Application && $resource->git_based())
            @can('update', $resource)
                <div class="w-full sm:w-96">
                    <x-forms.listbox id="isPreviewSuffixEnabled" :label="__('common.pr_deployment_suffix')"
                        :helper="__('common.pr_deployment_suffix_helper')"
                        onChange="instantSave" :options="[
                            ['value' => true, 'label' => __('common.add_suffix')],
                            ['value' => false, 'label' => __('common.share_path')],
                        ]" />
                </div>
            @endcan
        @endif
        <form wire:submit='submit' class="flex flex-col gap-4">
            <x-unsaved-bar action="submit" />
            @if (!$isReadOnly)
                @can('update', $resource)
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($fileStorage->is_host_file)
                            <x-modal-confirmation :ignoreWire="false" :title="__('common.confirm_host_file_mount_removal')"
                                :buttonTitle="__('common.delete')" isErrorButton submitAction="delete" :checkboxes="$hostFileDeletionCheckboxes"
                                :actions="[__('common.host_file_mount_removal_action')]"
                                confirmationText="{{ $fs_path }}"
                                :confirmationLabel="__('common.confirmation_label').' '.__('common.filepath')"
                                :shortConfirmationLabel="__('common.filepath')" />
                        @elseif ($fileStorage->is_directory)
                            <x-modal-confirmation :ignoreWire="false" :title="__('common.confirm_directory_conversion')"
                                :buttonTitle="__('common.convert_to_file')" submitAction="convertToFile" :actions="[
                                    __('common.directory_conversion_action'),
                                ]"
                                confirmationText="{{ $fs_path }}"
                                :confirmationLabel="__('common.confirmation_label').' '.__('common.filepath')"
                                :shortConfirmationLabel="__('common.filepath')" :confirmWithPassword="false" :step2ButtonText="__('common.convert_to_file')" />
                            @if ($resource instanceof \App\Models\Application)
                                <x-modal-input :buttonTitle="__('common.configure_backup')" :title="__('common.configure_directory_backup')"
                                    :wireIgnore="false">
                                    <livewire:project.application.backup.create :application="$resource"
                                        :selected-target-key="'directory:' . $fileStorage->id"
                                        wire:key="configure-directory-backup-{{ $fileStorage->id }}" />
                                </x-modal-input>
                            @endif
                            <x-modal-confirmation :ignoreWire="false" :title="__('common.confirm_directory_deletion')" :buttonTitle="__('common.delete')"
                                isErrorButton submitAction="delete" :checkboxes="$directoryDeletionCheckboxes" :actions="[
                                    __('common.directory_deletion_action'),
                                ]"
                                confirmationText="{{ $fs_path }}"
                                :confirmationLabel="__('common.confirmation_label').' '.__('common.filepath')"
                                :shortConfirmationLabel="__('common.filepath')" />
                        @else
                            @if (!$fileStorage->is_binary && !$fileStorage->is_too_large)
                                <x-modal-confirmation :ignoreWire="false" :title="__('common.confirm_file_conversion')"
                                    :buttonTitle="__('common.convert_to_directory')" submitAction="convertToDirectory" :actions="[
                                        __('common.file_conversion_action'),
                                    ]"
                                    confirmationText="{{ $fs_path }}"
                                    :confirmationLabel="__('common.confirmation_label').' '.__('common.filepath')"
                                    :shortConfirmationLabel="__('common.filepath')" :confirmWithPassword="false"
                                    :step2ButtonText="__('common.convert_to_directory')" />
                            @endif
                            <x-forms.button type="button" wire:click="loadStorageOnServer">{{ __('common.load_from_server') }}</x-forms.button>
                            <x-modal-confirmation :ignoreWire="false" :title="__('common.confirm_file_deletion')" :buttonTitle="__('common.delete')"
                                isErrorButton submitAction="delete" :checkboxes="$fileDeletionCheckboxes" :actions="[__('common.file_deletion_action')]"
                                confirmationText="{{ $fs_path }}"
                                :confirmationLabel="__('common.confirmation_label').' '.__('common.filepath')"
                                :shortConfirmationLabel="__('common.filepath')" />
                        @endif
                    </div>
                @endcan
                @if (!$fileStorage->is_directory && !$fileStorage->is_host_file)
                    @can('update', $resource)
                        @if (data_get($resource, 'settings.is_preserve_repository_enabled'))
                            <div class="w-full sm:w-96">
                                <x-forms.checkbox instantSave :label="__('common.based_on_git_repository')"
                                    id="isBasedOnGit"></x-forms.checkbox>
                            </div>
                        @endif
                        <x-forms.textarea
                            :label="$fileStorage->is_based_on_git ? __('common.content_refreshed_after_deployment') : __('common.content')"
                            :helper="__('common.content_may_be_outdated')"
                            rows="20" id="content"
                            readonly="{{ $fileStorage->is_based_on_git || $fileStorage->is_binary || $fileStorage->is_too_large }}"></x-forms.textarea>
                    @else
                        @if (data_get($resource, 'settings.is_preserve_repository_enabled'))
                            <div class="w-full sm:w-96">
                                <x-forms.checkbox disabled :label="__('common.based_on_git_repository')"
                                    id="isBasedOnGit"></x-forms.checkbox>
                            </div>
                        @endif
                        <x-forms.textarea
                            :label="$fileStorage->is_based_on_git ? __('common.content_refreshed_after_deployment') : __('common.content')"
                            :helper="__('common.content_may_be_outdated')"
                            rows="20" id="content" disabled></x-forms.textarea>
                    @endcan
                @endif
            @else
                {{-- Read-only view --}}
                @if (!$fileStorage->is_directory && !$fileStorage->is_host_file)
                    @can('update', $resource)
                        <div class="flex gap-2">
                            <x-forms.button type="button" wire:click="loadStorageOnServer">{{ __('common.load_from_server') }}</x-forms.button>
                        </div>
                    @endcan
                    @if (data_get($resource, 'settings.is_preserve_repository_enabled'))
                        <div class="w-full sm:w-96">
                            <x-forms.checkbox disabled :label="__('common.based_on_git_repository')"
                                id="isBasedOnGit"></x-forms.checkbox>
                        </div>
                    @endif
                    <x-forms.textarea
                        :label="$fileStorage->is_based_on_git ? __('common.content_refreshed_after_deployment') : __('common.content')"
                        :helper="__('common.content_may_be_outdated')"
                        rows="20" id="content" disabled></x-forms.textarea>
                @endif
            @endif
        </form>
        @if ($isReadOnly && $fileStorage->is_directory && $resource instanceof \App\Models\Application)
            @can('update', $resource)
                <div>
                    <x-modal-input :buttonTitle="__('common.configure_backup')" :title="__('common.configure_directory_backup')" :wireIgnore="false">
                        <livewire:project.application.backup.create :application="$resource"
                            :selected-target-key="'directory:' . $fileStorage->id"
                            wire:key="configure-readonly-directory-backup-{{ $fileStorage->id }}" />
                    </x-modal-input>
                </div>
            @endcan
        @endif
    </div>
</div>
