@if ($availableS3Storages->isEmpty())
    <x-application.settings-section :title="__('common.s3_storage')"
        :description="__('common.s3_backup_destination_description')" flush>
        <x-empty :title="__('common.no_validated_s3_storage')"
            :description="__('common.add_validate_s3_storage')"
            icon-name="storages">
            <x-slot:contents>
                <a class="button" {{ wireNavigate() }} href="{{ route('storage.index') }}">{{ __('common.open_s3_storage') }}</a>
            </x-slot:contents>
        </x-empty>
    </x-application.settings-section>
@else
    <form wire:submit="submit">
        <x-unsaved-bar action="submit" />

        <x-application.settings-section :title="__('common.s3_storage')"
            :description="__('common.remote_backup_storage_description')">
            <x-slot:actions>
                @if (! $saveS3)
                    <x-forms.button type="button" wire:click="toggleS3" wire:loading.attr="disabled"
                        wire:target="toggleS3" isHighlighted>{{ __('common.enable_s3') }}</x-forms.button>
                @else
                    <x-forms.button type="button" wire:click="toggleS3" wire:loading.attr="disabled"
                        wire:target="toggleS3">{{ __('common.disable_s3') }}</x-forms.button>
                @endif
            </x-slot:actions>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-forms.listbox id="s3StorageId" :label="__('common.s3_storage')" portal :required="$saveS3"
                    :options="$availableS3Storages->map(fn ($s3) => [
                        'value' => $s3->id,
                        'label' => $s3->name,
                    ])->values()->all()" />
                <x-forms.listbox id="disableLocalBackup" :label="__('common.local_copy')" portal :disabled="! $saveS3"
                    :options="[
                        ['value' => false, 'label' => __('common.keep_local_backup')],
                        ['value' => true, 'label' => __('common.delete_after_s3_upload')],
                    ]" />
            </div>
        </x-application.settings-section>
    </form>
@endif
