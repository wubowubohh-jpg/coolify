<form class="application-settings-form flex w-full flex-col gap-4" wire:submit="submit">
    <x-forms.input id="name" :label="__('common.name')" required />
    <x-forms.input id="description" :label="__('common.description')" />
    <div class="flex justify-end">
        <x-forms.button type="submit"
            defaultClass="button button-highlighted">
            {{ __('common.create_team') }}
        </x-forms.button>
    </div>
</form>
