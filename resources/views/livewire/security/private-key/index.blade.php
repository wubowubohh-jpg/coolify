<div>
    <x-slot:title>
        {{ __('common.keys_tokens') }} | Coolify
    </x-slot>

    <x-security.settings-layout>
        <x-application.settings-section :title="__('common.private_keys')"
            :description="__('common.generate_or_add_ssh')" flush>
        <x-slot:actions>
            @can('create', App\Models\PrivateKey::class)
                <x-modal-confirmation :title="__('common.confirm_unused_ssh_key_deletion')"
                    isErrorButton submitAction="cleanupUnusedKeys"
                    :actions="[__('common.unused_ssh_keys_deleted')]"
                    :confirmWithText="false" :confirmWithPassword="false">
                    <x-slot:trigger>
                        <button type="button"
                            class="button whitespace-nowrap text-error! hover:text-error! dark:text-error!">
                            <span class="max-sm:hidden">{{ __('common.delete_unused_keys') }}</span>
                            <span class="sm:hidden">{{ __('common.delete_unused') }}</span>
                        </button>
                    </x-slot:trigger>
                </x-modal-confirmation>

                <div x-data="{ dropdownOpen: false }" class="relative"
                    @click.outside="dropdownOpen = false" @keydown.escape.window="dropdownOpen = false">
                    <button type="button" @click="dropdownOpen = !dropdownOpen"
                        class="button whitespace-nowrap button-highlighted"
                        aria-haspopup="menu" :aria-expanded="dropdownOpen">
                        <x-reicon name="plus" class="size-3.5" />
                        <span class="max-sm:hidden">{{ __('common.new_private_key') }}</span>
                        <span class="sm:hidden">{{ __('common.new_key') }}</span>
                        <x-reicon name="chevron-down" class="size-3 opacity-55" />
                    </button>

                    <div x-cloak x-show="dropdownOpen" x-transition.origin.top.right role="menu"
                        class="listbox-panel left-auto! right-0! z-[90]! w-52! min-w-52!">
                        <button type="button" class="listbox-option justify-start! gap-2.5!"
                            wire:click="generatePrivateKey('ed25519')" @click="dropdownOpen = false"
                            role="menuitem">
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
                            <livewire:security.private-key.create :modal_mode="true" />
                        </x-modal-input>
                    </div>
                </div>
            @endcan
        </x-slot:actions>


    @if ($privateKeys->isEmpty())
        <x-empty :title="__('common.no_private_keys_yet')"
            :description="__('common.generate_or_add_ssh')"
            icon-name="keys" />
    @else
        <div>
            <div class="grid grid-cols-[minmax(0,1fr)_7rem_1.75rem] items-center gap-3 border-b border-neutral-200 bg-neutral-50 px-4 py-2.5 text-[13px] font-medium text-neutral-500 sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_7rem_1.75rem] dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-fg-faint">
                <div class="pl-11">{{ __('common.private_key_header') }}</div>
                <div class="hidden sm:block">{{ __('common.description') }}</div>
                <div class="text-center">{{ __('common.status') }}</div>
                <div class="w-7"></div>
            </div>
            @foreach ($privateKeys as $key)
                @can('view', $key)
                    <div wire:key="private-key-{{ $key->id }}"
                        class="border-b border-neutral-200 last:border-b-0 dark:border-white/[0.07]">
                    <div
                        class="grid min-h-14 w-full grid-cols-[minmax(0,1fr)_7rem_1.75rem] items-center gap-3 px-4 py-2.5 text-left transition-colors hover:bg-neutral-50 sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_7rem_1.75rem] dark:hover:bg-white/[0.025]"
                    >
                        <div class="flex min-w-0 items-center gap-3">
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.04] dark:text-fg-dim">
                                <x-reicon name="keys" class="size-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-[13px]! leading-4! font-semibold! text-black dark:text-fg">
                                    {{ data_get($key, 'name') }}
                                </h3>
                            </div>
                        </div>
                        <p class="hidden truncate text-[12px] text-neutral-500 sm:block dark:text-fg-dim">
                            {{ $key->description ?: '-' }}
                        </p>
                        <div class="flex justify-center">
                            @if ($key->isInUse())
                                <x-status-badge :label="__('common.in_use')" type="success" />
                            @else
                                <x-status-badge :label="__('common.unused')" type="warning" />
                            @endif
                        </div>
                        <button type="button" class="icon-button" :title="__('common.edit_private_key')"
                            aria-label="{{ __('common.edit_key') }} {{ $key->name }}"
                            @click="$dispatch('open-private-key-editor', { name: @js($key->name), description: @js($key->description ?? '') })"
                            wire:click="openEditor('{{ $key->uuid }}')">
                            <x-reicon name="settings" class="size-3.5" />
                        </button>
                    </div>
                    </div>
                @else
                    <div class="grid min-h-14 cursor-not-allowed grid-cols-[minmax(0,1fr)_7rem_1.75rem] items-center gap-3 border-b border-neutral-200 px-4 py-2.5 opacity-65 last:border-b-0 sm:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_7rem_1.75rem] dark:border-white/[0.07]"
                        :title="__('common.no_permission_private_key')">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-neutral-200 bg-white text-neutral-400 dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-fg-faint">
                                <x-reicon name="keys" class="size-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-[13px]! leading-4! font-semibold! text-black dark:text-fg">
                                    {{ data_get($key, 'name') }}
                                </h3>
                            </div>
                        </div>
                        <p class="hidden truncate text-[12px] text-neutral-500 sm:block dark:text-fg-dim">{{ $key->description ?: '-' }}</p>
                        <div class="flex flex-wrap justify-center gap-2">
                            <x-status-badge :label="__('common.view_only')" type="neutral" />
                            @if (!$key->isInUse())
                                <x-status-badge :label="__('common.unused')" type="warning" />
                            @endif
                        </div>
                        <span class="size-7"></span>
                    </div>
                @endcan
            @endforeach
        </div>
    @endif

    <x-modal-input :title="__('common.edit_private_key')" :wireIgnore="false" :contentClicks="false"
        @open-private-key-editor.window="modalOpen=true; $nextTick(() => { $refs.loadingPrivateKeyName.value = $event.detail.name; $refs.loadingPrivateKeyDescription.value = $event.detail.description })">
        <x-slot:content><span class="hidden" aria-hidden="true"></span></x-slot:content>
        <div wire:loading.flex wire:target="openEditor" :aria-label="__('common.loading_private_key_editor')"
            class="w-full flex-col gap-4">
            <div class="grid gap-4 lg:grid-cols-2">
                <x-forms.input :label="__('common.name')" required x-ref="loadingPrivateKeyName" />
                <x-forms.input :label="__('common.description')" x-ref="loadingPrivateKeyDescription" />
                <div class="lg:col-span-2">
                    <x-forms.input :label="__('common.public_key')" loading
                        :helper="__('common.copy_authorized_keys')" />
                </div>
                <div class="lg:col-span-2">
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <label class="text-[13px] font-medium">{{ __('common.private_key_header') }} <span class="text-helper">*</span></label>
                        <span class="text-[11px] font-medium text-neutral-400 dark:text-fg-faint">{{ __('common.edit_key') }}</span>
                    </div>
                    <x-forms.input loading :allowToPeak="false" />
                </div>
            </div>
            <div class="flex items-center justify-between gap-2 border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                <x-forms.button disabled isError>{{ __('common.delete') }}</x-forms.button>
                <x-forms.button disabled isHighlighted>{{ __('common.save_changes') }}</x-forms.button>
            </div>
        </div>
        @if ($selectedPrivateKeyUuid)
            <div wire:loading.remove wire:target="openEditor">
                <livewire:security.private-key.show :private_key_uuid="$selectedPrivateKeyUuid" :modalMode="true"
                    :key="'private-key-editor-'.$selectedPrivateKeyUuid" />
            </div>
        @endif
    </x-modal-input>
        </x-application.settings-section>

    </x-security.settings-layout>
</div>
