<div class="flex items-center gap-2">
    @can('update', $server)
        <x-modal-input :buttonTitle="__('common.edit')" :title="__('common.edit_configuration')">
            <livewire:server.proxy.new-dynamic-configuration :server_id="$server_id" :fileName="$fileName" :value="$value"
                :newFile="$newFile" wire:key="{{ $fileName }}" />
        </x-modal-input>
        <x-forms.button isError wire:click="delete('{{ $fileName }}')">{{ __('common.delete') }}</x-forms.button>
    @endcan
</div>
