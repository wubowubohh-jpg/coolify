<x-layout>
    @if ($private_keys->count() === 0)
        <h1>{{ __('common.create_private_key') }}</h1>
        <div class="subtitle">{{ __('common.private_key_required_before_server') }}</div>
        <livewire:private-key.create from="server" />
    @else
        <livewire:server.new.by-ip :private_keys="$private_keys" :limit_reached="$limit_reached" />
    @endif
</x-layout>
