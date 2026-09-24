<div class="w-full">
    @if ($limit_reached)
        <x-limit-reached name="servers" />
    @else
        @php
            $privateKeyOptions = $private_keys
                ->map(fn ($key) => ['value' => $key->id, 'label' => $key->name])
                ->values()
                ->all();
        @endphp

        <form wire:submit="submit">
            <x-application.settings-section :title="__('common.connect_server')"
                :description="__('common.connect_server_description')">
                <x-slot:actions>
                    <button type="submit"
                        class="button button-highlighted">
                        {{ __('common.continue') }}
                        <x-reicon name="arrow-right" class="size-3.5" />
                    </button>
                </x-slot:actions>

                <div class="mb-5">
                    <x-forms.input id="ip" :label="__('common.ip_address_or_domain')" required
                        :helper="__('common.server_ip_helper')" />
                </div>

                <div class="mb-5">
                    <div class="flex items-end gap-3">
                        <div class="min-w-0 flex-1">
                            <x-forms.listbox id="private_key_id" :label="__('common.private_key')"
                                :placeholder="__('common.select_private_key')" :options="$privateKeyOptions" />
                        </div>
                        @can('create', App\Models\PrivateKey::class)
                            <div x-data="{ dropdownOpen: false }" class="relative shrink-0"
                                @click.outside="dropdownOpen = false"
                                @keydown.escape.window="dropdownOpen = false">
                                <button type="button" class="button" @click="dropdownOpen = !dropdownOpen"
                                    aria-haspopup="menu" :aria-expanded="dropdownOpen">
                                    <x-reicon name="plus" class="size-3.5" />
                                    {{ __('common.new_key') }}
                                    <x-reicon name="chevron-down" class="size-3 opacity-55" />
                                </button>
                                <div x-cloak x-show="dropdownOpen" x-transition.origin.top.right role="menu"
                                    class="listbox-panel left-auto! right-0! z-[90]! w-52! min-w-52!">
                                    <button type="button" class="listbox-option justify-start! gap-2.5!"
                                        wire:click="generatePrivateKey('ed25519')"
                                        @click="dropdownOpen = false" role="menuitem">
                                        <x-reicon name="keys" class="size-3.5 shrink-0 opacity-70" />
                                        {{ __('common.generate_ed25519') }}
                                    </button>
                                    <button type="button" class="listbox-option justify-start! gap-2.5!"
                                        wire:click="generatePrivateKey('rsa')" @click="dropdownOpen = false"
                                        role="menuitem">
                                        <x-reicon name="keys" class="size-3.5 shrink-0 opacity-70" />
                                        {{ __('common.generate_rsa') }}
                                    </button>
                                    <x-modal-input :title="__('common.add_private_key_manually')">
                                        <x-slot:content>
                                            <button type="button" @click="dropdownOpen = false"
                                                class="listbox-option justify-start! gap-2.5!" role="menuitem">
                                                <x-reicon name="plus" class="size-3.5 shrink-0 opacity-70" />
                                                {{ __('common.add_manually') }}
                                            </button>
                                        </x-slot:content>
                                        <livewire:security.private-key.create :modal_mode="true" from="server" />
                                    </x-modal-input>
                                </div>
                            </div>
                        @endcan
                    </div>
                </div>

                <div class="grid gap-4 border-t border-neutral-200 pt-4 lg:grid-cols-2 dark:border-white/[0.08]">
                    <x-forms.input id="name" :label="__('common.name')" required />
                    <x-forms.input id="description" :label="__('common.description')" />
                </div>

                <x-forms.collapsible class="mt-5 border-t border-neutral-200 pt-4 dark:border-white/[0.08]"
                    content-class="flex flex-col gap-4">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-forms.input id="user" :label="__('common.user')" required
                            :helper="__('common.non_root_ssh_users_experimental')" />
                        <x-forms.input type="number" id="port" :label="__('common.port')" required />
                    </div>
                    <x-forms.listbox id="is_build_server"
                        :helper="__('common.dedicated_build_server_helper')"
                        :label="__('common.dedicated_build_server')" :options="[
                            ['value' => false, 'label' => __('common.no')],
                            ['value' => true, 'label' => __('common.yes')],
                        ]" />
                </x-forms.collapsible>
            </x-application.settings-section>
        </form>
    @endif
</div>
