@props([
    'title' => null,
    'subtitle' => null,
    // Hide family H1 only at xl+; keep create in the layer-2 bar.
    'titleOnDesktop' => false,
])

@php
    $title ??= __('common.team');
    $subtitle ??= __('common.team_description');
@endphp

<x-dashboard.navbar section="team" :title="$title" :subtitle="$subtitle" :titleOnDesktop="$titleOnDesktop">
    @isset($titleActions)
        <x-slot:titleActions>
            {{ $titleActions }}
        </x-slot:titleActions>
    @endisset
    <x-slot:actions>
        @isset($actions)
            {{ $actions }}
        @else
            <x-modal-input :title="__('common.new_team')">
                <x-slot:content>
                    <button type="button"
                        class="button button-highlighted">
                        <x-reicon name="plus" class="size-3.5" />
                        {{ __('common.new_team_lower') }}
                    </button>
                </x-slot:content>
                <livewire:team.create />
            </x-modal-input>
        @endisset
    </x-slot:actions>
</x-dashboard.navbar>
