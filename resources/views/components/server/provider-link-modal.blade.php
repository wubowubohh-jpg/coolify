@props([
    'server',
    'provider',
    'providerLabel',
    'tokenModel',
    'tokens',
    'manualModel',
    'manualLabel',
    'manualPlaceholder',
    'searchByIdMethod',
    'searchByIpMethod',
    'linkMethod',
    'searchError' => null,
    'noMatch' => false,
    'matched' => null,
])

@php
    $tokenOptions = $tokens->map(fn ($token) => [
        'value' => $token->id,
        'label' => $token->name,
    ])->values()->all();
@endphp

<x-modal-input :title="__('common.provider_link_to', ['provider' => $providerLabel])" :wireIgnore="false">
    <x-slot:content>
        <button type="button"
            class="flex h-8 w-full items-center gap-2 rounded-md px-2 text-left text-[12px] text-neutral-600 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-dim dark:hover:bg-white/[0.06] dark:hover:text-fg">
            <x-reicon name="plus" class="size-3.5" />
            {{ $providerLabel }}
        </button>
    </x-slot:content>

    <div class="application-settings-form flex flex-col gap-4">
        <p class="text-[12px] leading-5 text-neutral-500 dark:text-fg-dim">
            {{ __('common.provider_link_description', ['provider' => $providerLabel]) }}
        </p>

        <x-forms.listbox :id="$tokenModel" :label="__('common.provider_token', ['provider' => $providerLabel])"
            :placeholder="__('common.select_token')" :options="$tokenOptions" live />

        <div class="grid items-end gap-2 sm:grid-cols-[minmax(0,1fr)_auto]">
            <x-forms.input :id="$manualModel" :label="$manualLabel"
                :placeholder="$manualPlaceholder" />
            <button type="button" class="button" wire:click="{{ $searchByIdMethod }}"
                wire:loading.attr="disabled" wire:target="{{ $searchByIdMethod }}">
                <span wire:loading.remove wire:target="{{ $searchByIdMethod }}">{{ __('common.search_id') }}</span>
                <span wire:loading wire:target="{{ $searchByIdMethod }}">{{ __('common.searching') }}</span>
            </button>
        </div>

        <div class="flex items-center gap-3 text-[10px] uppercase tracking-wide text-neutral-400 dark:text-fg-faint">
            <span class="h-px flex-1 bg-neutral-200 dark:bg-white/[0.08]"></span>
            {{ __('onboarding.or') }}
            <span class="h-px flex-1 bg-neutral-200 dark:bg-white/[0.08]"></span>
        </div>

        <button type="button" class="button justify-center" wire:click="{{ $searchByIpMethod }}"
            wire:loading.attr="disabled" wire:target="{{ $searchByIpMethod }}">
            <span wire:loading.remove wire:target="{{ $searchByIpMethod }}">{{ __('common.search_by_server_ip') }}</span>
            <span wire:loading wire:target="{{ $searchByIpMethod }}">{{ __('common.searching') }}</span>
        </button>

        @if ($searchError)
            <x-callout type="error" :title="__('common.provider_search_failed')">{{ $searchError }}</x-callout>
        @elseif ($noMatch)
            <x-callout type="warning" :title="__('common.no_matching_resource')">
                {{ __('common.provider_no_match_description') }}
            </x-callout>
        @elseif ($matched)
            <div
                class="rounded-lg border border-success/25 bg-success/5 p-3 dark:border-success/20 dark:bg-success/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-[13px] font-semibold text-black dark:text-fg">
                            {{ $matched['name'] ?? __('common.matched_resource') }}
                        </p>
                        <p class="mt-1 text-[11px] text-neutral-500 dark:text-fg-dim">
                            {{ $matched['id'] ?? __('common.unknown_id') }}
                            @if ($matched['status'] ?? null)
                                <span class="px-1 text-neutral-300 dark:text-white/15">·</span>
                                {{ ucfirst($matched['status']) }}
                            @endif
                        </p>
                    </div>
                    <x-status-badge :label="__('common.match_found')" type="success" />
                </div>
                <button type="button"
                    class="button mt-3 button-highlighted"
                    wire:click="{{ $linkMethod }}">
                    {{ __('common.link_resource') }}
                </button>
            </div>
        @endif
    </div>
</x-modal-input>
