<div x-show="filteredItems.length === 0" class="flex min-h-52 flex-col items-center justify-center rounded-xl border border-neutral-200 bg-white px-6 text-center dark:border-white/[0.08] dark:bg-white/[0.05]">
    <x-reicon name="search" class="mb-3 size-6 text-neutral-300 dark:text-fg-faint" />
    @php
        $labelTranslations = [
            'destinations' => 'common.destinations',
            'sources' => 'common.sources',
        ];
        $translatedLabel = __($labelTranslations[$label] ?? $label);
    @endphp
    <p class="text-[13px] font-medium">{{ __('common.no_matching', ['label' => $translatedLabel]) }}</p>
    <p class="mt-1 text-[12px] text-neutral-500 dark:text-fg-dim">{{ __('common.try_different_search') }}</p>
</div>
