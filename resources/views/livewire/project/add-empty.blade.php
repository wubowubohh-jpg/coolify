<form class="space-y-4" wire:submit="submit">
    <div class="grid gap-4 md:grid-cols-2">
        <x-forms.input :placeholder="__('common.project_name_placeholder')" id="name" :label="__('common.name')" required />
        <x-forms.input :placeholder="__('common.project_description_placeholder')" id="description" :label="__('common.description')" />
    </div>

    <p
        class="rounded-lg border border-neutral-200 bg-neutral-50 px-3 py-2.5 text-[12px] text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-fg-dim">
        {{ __('common.production_environment_created_automatically') }}
    </p>

    <footer class="flex justify-end border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
        <x-forms.button type="submit"
            defaultClass="button button-highlighted">
            {{ __('common.create_project') }}
        </x-forms.button>
    </footer>
</form>
