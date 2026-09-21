<div>
    <x-slot:title>
        {{ data_get_str($storage, 'name')->limit(20) }} | S3 Storage | Coolify
    </x-slot>

    @php
        $storageRouteParameters = ['storage_uuid' => $storage->uuid];
        $showSettingsSidebar = in_array($currentRoute, ['storage.show', 'storage.resources', 'storage.danger'], true);
        $settingsMenuItems = [
            [
                'label' => __('common.general'),
                'route' => 'storage.show',
                'active' => $currentRoute === 'storage.show',
                'icon' => 'settings',
            ],
            [
                'label' => __('common.resources'),
                'route' => 'storage.resources',
                'active' => $currentRoute === 'storage.resources',
                'icon' => 'grid',
            ],
            [
                'label' => __('common.danger_zone'),
                'route' => 'storage.danger',
                'active' => $currentRoute === 'storage.danger',
                'icon' => 'shield-alert',
            ],
        ];
    @endphp

    <x-dashboard.navbar section="storage" :parameters="$storageRouteParameters"
        :title="$storage->name"
        :subtitle="filled($storage->description) ? $storage->description : __('common.s3_compatible_backup_destination')"
        :mobileTitleOnly="true" />

    @if ($showSettingsSidebar)
        <section class="application-settings-workspace mt-4 w-full max-w-none lg:mt-0">
            <div class="grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
                <aside class="application-settings-navigation min-w-0 xl:self-start">
                    <nav :aria-label="__('common.s3_storage_settings')"
                        class="grid grid-cols-2 gap-0.5 border-y border-neutral-200 py-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-1 xl:border-y-0 xl:py-0 dark:border-white/[0.06]">
                        <div class="nav-section hidden xl:block">{{ __('common.settings') }}</div>
                        @foreach ($settingsMenuItems as $menuItem)
                            <a wire:key="storage-settings-{{ str($menuItem['label'])->slug() }}"
                                @class([
                                    'menu-item',
                                    'menu-item-active' => $menuItem['active'],
                                ])
                                {{ wireNavigate() }}
                                href="{{ route($menuItem['route'], $storageRouteParameters) }}">
                                <x-reicon :name="$menuItem['icon']" class="menu-item-icon" />
                                <span class="menu-item-label">{{ $menuItem['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>

                <div class="min-w-0">
                    @if ($currentRoute === 'storage.show')
                        <livewire:storage.form :storage="$storage" />
                    @elseif ($currentRoute === 'storage.resources')
                        <livewire:storage.resources :storage="$storage" :key="'resources-'.$storage->uuid" />
                    @elseif ($currentRoute === 'storage.danger')
                        <div class="application-settings-form">
                            <x-application.settings-section id="storage-danger-section" :title="__('common.danger_zone')"
                                :helper="__('common.s3_storage_delete_description')">
                                <x-danger-zone :title="__('common.delete_storage')">
                                            <p>
                                                {!! __('common.permanently_delete_storage_description', ['name' => '<strong class="font-semibold text-black dark:text-fg">'.e($storage->name).'</strong>']) !!}
                                            </p>
                                            <ul class="space-y-1 text-xs">
                                                <li>• {{ __('common.backup_schedules_stop_writing') }}</li>
                                                @if ($backupCount > 0)
                                                    <li>• {{ __('common.backup_schedules_use_destination', ['count' => $backupCount]) }}</li>
                                                @endif
                                                <li>• {{ __('common.bucket_contents_untouched') }}</li>
                                                <li>• {{ __('common.storage_cannot_restore') }}</li>
                                            </ul>
                                        <x-slot:action>
                                            @can('delete', $storage)
                                                <x-modal-confirmation :title="__('common.confirm_storage_deletion')" isErrorButton
                                                    :buttonTitle="__('common.delete')" submitAction="delete"
                                                    :actions="array_filter([
                                                        __('common.selected_storage_deleted'),
                                                        $backupCount > 0
                                                            ? __('common.storage_schedules_stop_saving', ['count' => $backupCount])
                                                            : null,
                                                    ])"
                                                    confirmationText="{{ $storage->name }}"
                                                    :confirmationLabel="__('common.confirm_storage_name')"
                                                    :shortConfirmationLabel="__('common.storage_name')" :confirmWithPassword="false"
                                                    :step2ButtonText="__('common.permanently_delete_button')" />
                                            @else
                                                <x-forms.button isError disabled :tooltip="__('common.no_permission_delete_storage')">
                                                    {{ __('common.delete') }}
                                                </x-forms.button>
                                            @endcan
                                        </x-slot:action>
                                </x-danger-zone>

                                @cannot('delete', $storage)
                                    <div class="mt-4">
                                        <x-callout type="danger" :title="__('common.insufficient_permissions')">
                                            {{ __('common.storage_delete_admin_help') }}
                                        </x-callout>
                                    </div>
                                @endcannot
                            </x-application.settings-section>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif
</div>
