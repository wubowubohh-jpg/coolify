@php
    $networkAliases = str($application->custom_network_aliases ?? '')
        ->explode(',')
        ->map(fn ($alias) => trim($alias))
        ->filter()
        ->values();
    $exposedPorts = str($application->ports_exposes ?? '')
        ->explode(',')
        ->map(fn ($port) => trim($port))
        ->filter()
        ->implode(', ');
@endphp

<section id="internal-access-section" class="pt-5" wire:init="loadCurrentInternalHostname">
    <h3 class="mb-4 text-sm font-semibold text-black dark:text-fg">{{ __('common.internal_access') }}</h3>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @if ($currentInternalHostname)
            <x-forms.copy-button :label="__('common.internal_hostname')" :text="$currentInternalHostname" />
        @else
            <div class="w-full">
                <label class="mb-1 flex items-center gap-1 text-sm font-medium text-black dark:text-white">{{ __('common.internal_hostname') }}</label>
                <input type="text"
                    value="{{ $currentInternalHostnameLoaded ? __('common.no_deployed_container') : __('common.loading') }}"
                    class="input input-with-copy-button bg-white dark:bg-coolgray-100 dark:read-only:bg-coolgray-100 dark:read-only:text-white"
                    readonly aria-live="polite">
            </div>
        @endif
        <x-forms.copy-button :label="__('common.docker_network')" :text="$application->destination->network" />
        <x-forms.copy-button :label="__('common.exposed_ports')" :text="$exposedPorts ?: __('common.none')" />
        <x-forms.copy-button :label="__('common.network_aliases')" :text="$networkAliases->implode(', ') ?: __('common.none')" />
    </div>
    <div class="mt-4 flex flex-col gap-3 border-t border-neutral-200 pt-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/[0.07]">
        <p class="text-sm text-neutral-500 dark:text-fg-dim">
            {{ __('common.internal_access_description') }}
        </p>
        <button type="button" class="button shrink-0"
            @click="window.scrollToSettingsSection?.('networking-section')">
            {{ __('common.edit_networking') }}
        </button>
    </div>
</section>
