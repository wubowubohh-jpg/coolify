@php
    $hasVolumes = $this->volumeCount > 0;
    $hasFiles = $this->fileCount > 0;
    $hasDirectories = $this->directoryCount > 0;
    $tabButtonBase = 'h-7 rounded-md px-2.5 text-[12px] font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-40';
    $tabButtonActive = 'bg-white text-black shadow-sm ring-1 ring-neutral-200 dark:bg-white/[0.09] dark:text-fg dark:ring-white/[0.08]';
    $tabButtonInactive = 'text-neutral-500 hover:text-black dark:text-fg-faint dark:hover:text-fg';
@endphp

<div class="flex flex-col gap-6">
    @if (
        $resource->getMorphClass() == 'App\Models\Application' ||
            $resource->getMorphClass() == 'App\Models\StandalonePostgresql' ||
            $resource->getMorphClass() == 'App\Models\StandaloneRedis' ||
            $resource->getMorphClass() == 'App\Models\StandaloneMariadb' ||
            $resource->getMorphClass() == 'App\Models\StandaloneKeydb' ||
            $resource->getMorphClass() == 'App\Models\StandaloneDragonfly' ||
            $resource->getMorphClass() == 'App\Models\StandaloneClickhouse' ||
            $resource->getMorphClass() == 'App\Models\StandaloneMongodb' ||
            $resource->getMorphClass() == 'App\Models\StandaloneMysql')
        <x-application.settings-section id="storage-mounts-section" :title="__('common.persistent_storage')" :flush="true"
            :helper="$resource instanceof \App\Models\Application && $resource->git_based()
                ? __('common.preview_deployment_volumes_helper')
                : __('common.mount_storage_helper')">
            <x-slot:actions>
                @if ($resource?->build_pack !== 'dockercompose')
                    @can('update', $resource)
                        <div x-data="{
                            dropdownOpen: false,
                            volumeModalOpen: false,
                            fileModalOpen: false,
                            hostFileModalOpen: false,
                            directoryModalOpen: false
                        }"
                            @close-storage-modal.window="
                            if ($event.detail === 'volume') volumeModalOpen = false;
                            if ($event.detail === 'file') fileModalOpen = false;
                            if ($event.detail === 'host-file') hostFileModalOpen = false;
                            if ($event.detail === 'directory') directoryModalOpen = false;
                        ">
                            <div class="relative" @click.outside="dropdownOpen = false">
                                <x-forms.button
                                    class="button-highlighted"
                                    @click="dropdownOpen = !dropdownOpen" aria-haspopup="menu"
                                    x-bind:aria-expanded="dropdownOpen">
                                    <x-reicon name="plus" class="size-3.5" />
                                    {{ __('common.add_mount') }}
                                    <x-reicon name="chevron-down" class="size-3 opacity-55" />
                                </x-forms.button>

                                <div x-show="dropdownOpen" x-cloak role="menu"
                                    x-transition.origin.top.left
                                    class="listbox-panel left-0! right-auto! z-[90]! w-52! min-w-52! sm:left-auto! sm:right-0!">
                                    <button type="button" class="listbox-option justify-start! gap-2.5!" role="menuitem"
                                        @click="volumeModalOpen = true; dropdownOpen = false">
                                        <x-reicon name="storages" class="size-3.5 shrink-0 opacity-70" />
                                        {{ __('common.volume_mount') }}
                                    </button>
                                    <button type="button" class="listbox-option justify-start! gap-2.5!" role="menuitem"
                                        @click="fileModalOpen = true; dropdownOpen = false">
                                        <x-reicon name="file" class="size-3.5 shrink-0 opacity-70" />
                                        {{ __('common.file_mount') }}
                                    </button>
                                    <button type="button" class="listbox-option justify-start! gap-2.5!" role="menuitem"
                                        @click="hostFileModalOpen = true; dropdownOpen = false">
                                        <x-reicon name="file-content" class="size-3.5 shrink-0 opacity-70" />
                                        {{ __('common.host_file_mount') }}
                                    </button>
                                    <button type="button" class="listbox-option justify-start! gap-2.5!" role="menuitem"
                                        @click="directoryModalOpen = true; dropdownOpen = false">
                                        <x-reicon name="folder" class="size-3.5 shrink-0 opacity-70" />
                                        {{ __('common.directory_mount') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Volume Modal --}}
                            <template x-teleport="body">
                                <div x-show="volumeModalOpen" @keydown.window.escape="volumeModalOpen=false"
                                    class="fixed top-0 left-0 lg:px-0 px-4 z-99 flex items-center justify-center w-screen h-screen">
                                    <div x-show="volumeModalOpen" x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" @click="volumeModalOpen=false"
                                        class="absolute inset-0 w-full h-full bg-black/20 backdrop-blur-xs"></div>
                                    <div x-show="volumeModalOpen" x-trap.inert.noscroll="volumeModalOpen"
                                        x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-2 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 -translate-y-2 sm:scale-95"
                                        class="application-settings-form application-settings-section relative w-full min-w-full lg:min-w-[36rem] lg:max-w-2xl">
                                        <header>
                                            <h3>{{ __('common.volume_mount') }}</h3>
                                            <button @click="volumeModalOpen=false"
                                                class="flex size-7 items-center justify-center rounded-md text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.07] dark:hover:text-fg">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </header>
                                        <div class="application-settings-section-body relative flex items-center justify-center w-auto"
                                            x-init="$watch('volumeModalOpen', value => {
                                                if (value) {
                                                    $nextTick(() => {
                                                        const input = $el.querySelector('input');
                                                        input?.focus();
                                                    })
                                                }
                                            })">
                                            <form class="flex w-full flex-col gap-4"
                                                wire:submit='submitPersistentVolume'>
                                                <p class="text-[13px] leading-5 text-neutral-500 dark:text-fg-dim">
                                                    {{ __('common.mount_docker_volume') }}
                                                </p>
                                                <div class="flex flex-col gap-4">
                                                    <x-forms.input canGate="update" :canResource="$resource" placeholder="pv-name"
                                                        id="name" :label="__('common.name')" required :helper="__('common.volume_name')" />
                                                    <x-forms.input canGate="update" :canResource="$resource"
                                                        placeholder="/tmp/root" id="mount_path" :label="__('common.destination_path')"
                                                        required :helper="__('common.directory_inside_container')" />
                                                    <div class="flex justify-end pt-2">
                                                        <x-forms.button canGate="update" :canResource="$resource" type="submit">
                                                            {{ __('common.add_volume') }}
                                                        </x-forms.button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- File Modal --}}
                            <template x-teleport="body">
                                <div x-show="fileModalOpen" @keydown.window.escape="fileModalOpen=false"
                                    class="fixed top-0 left-0 lg:px-0 px-4 z-99 flex items-center justify-center w-screen h-screen">
                                    <div x-show="fileModalOpen" x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" @click="fileModalOpen=false"
                                        class="absolute inset-0 w-full h-full bg-black/20 backdrop-blur-xs"></div>
                                    <div x-show="fileModalOpen" x-trap.inert.noscroll="fileModalOpen"
                                        x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-2 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 -translate-y-2 sm:scale-95"
                                        class="application-settings-form application-settings-section relative w-full min-w-full lg:min-w-[36rem] lg:max-w-2xl">
                                        <header>
                                            <h3>{{ __('common.file_mount') }}</h3>
                                            <button @click="fileModalOpen=false"
                                                class="flex size-7 items-center justify-center rounded-md text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.07] dark:hover:text-fg">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </header>
                                        <div class="application-settings-section-body relative flex items-center justify-center w-auto"
                                            x-init="$watch('fileModalOpen', value => {
                                                if (value) {
                                                    $nextTick(() => {
                                                        const input = $el.querySelector('input');
                                                        input?.focus();
                                                    })
                                                }
                                            })">
                                            <form class="flex w-full flex-col gap-4"
                                                x-data="{
                                                    hostPath: @js($this->fileStorageHostPath()),
                                                    filePath: @entangle('file_storage_path'),
                                                    previewPath() {
                                                        const path = (this.filePath || '').trim();

                                                        return this.hostPath + (path === '' ? '/' : (path.startsWith('/') ? path : `/${path}`));
                                                    },
                                                }"
                                                wire:submit='submitFileStorage'>
                                                <p class="text-[13px] leading-5 text-neutral-500 dark:text-fg-dim">
                                                    {{ __('common.create_managed_file') }}
                                                </p>
                                                <div class="flex flex-col gap-4">
                                                    <div class="rounded-lg bg-neutral-100 p-3 text-xs ring-1 ring-neutral-200 dark:bg-white/[0.04] dark:ring-white/[0.07]">
                                                        <div class="mb-1 font-medium">{{ __('common.host_file_path') }}</div>
                                                        <code class="break-all" x-text="previewPath()">{{ $this->fileStoragePreviewPath() }}</code>
                                                    </div>
                                                    <x-forms.input canGate="update" :canResource="$resource"
                                                        placeholder="/etc/nginx/nginx.conf" id="file_storage_path"
                                                        :label="__('common.destination_path')" required
                                                        x-on:input="filePath = $event.target.value"
                                                        :helper="__('common.file_location_inside_container')" />
                                                    <x-forms.textarea canGate="update" :canResource="$resource" :label="__('common.content')"
                                                        id="file_storage_content"></x-forms.textarea>
                                                    <div class="flex justify-end pt-2">
                                                        <x-forms.button canGate="update" :canResource="$resource" type="submit">
                                                            {{ __('common.add_file') }}
                                                        </x-forms.button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Host File Modal --}}
                            <template x-teleport="body">
                                <div x-show="hostFileModalOpen" @keydown.window.escape="hostFileModalOpen=false"
                                    class="fixed top-0 left-0 lg:px-0 px-4 z-99 flex items-center justify-center w-screen h-screen">
                                    <div x-show="hostFileModalOpen" x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" @click="hostFileModalOpen=false"
                                        class="absolute inset-0 w-full h-full bg-black/20 backdrop-blur-xs"></div>
                                    <div x-show="hostFileModalOpen" x-trap.inert.noscroll="hostFileModalOpen"
                                        x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-2 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 -translate-y-2 sm:scale-95"
                                        class="application-settings-form application-settings-section relative w-full min-w-full lg:min-w-[36rem] lg:max-w-2xl">
                                        <header>
                                            <h3>{{ __('common.host_file_mount') }}</h3>
                                            <button @click="hostFileModalOpen=false"
                                                class="flex size-7 items-center justify-center rounded-md text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.07] dark:hover:text-fg">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </header>
                                        <div class="application-settings-section-body relative flex items-center justify-center w-auto"
                                            x-init="$watch('hostFileModalOpen', value => {
                                                if (value) {
                                                    $nextTick(() => {
                                                        const input = $el.querySelector('input');
                                                        input?.focus();
                                                    })
                                                }
                                            })">
                                            <form class="flex w-full flex-col gap-4"
                                                wire:submit='submitHostFileStorage'>
                                                <p class="text-[13px] leading-5 text-neutral-500 dark:text-fg-dim">
                                                    {{ __('common.bind_existing_host_file') }}
                                                </p>
                                                <div class="flex flex-col gap-4">
                                                    <x-forms.input canGate="update" :canResource="$resource"
                                                        placeholder="/etc/nginx/nginx.conf"
                                                        id="host_file_storage_source" :label="__('common.host_file_path')" required
                                                        :helper="__('common.existing_host_file')" />
                                                    <x-forms.input canGate="update" :canResource="$resource"
                                                        placeholder="/etc/nginx/nginx.conf"
                                                        id="host_file_storage_destination" :label="__('common.destination_path')"
                                                        required :helper="__('common.file_location_inside_container')" />
                                                    <div class="flex justify-end pt-2">
                                                        <x-forms.button canGate="update" :canResource="$resource" type="submit">
                                                            {{ __('common.add_host_file') }}
                                                        </x-forms.button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Directory Modal --}}
                            <template x-teleport="body">
                                <div x-show="directoryModalOpen" @keydown.window.escape="directoryModalOpen=false"
                                    class="fixed top-0 left-0 lg:px-0 px-4 z-99 flex items-center justify-center w-screen h-screen">
                                    <div x-show="directoryModalOpen" x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" @click="directoryModalOpen=false"
                                        class="absolute inset-0 w-full h-full bg-black/20 backdrop-blur-xs"></div>
                                    <div x-show="directoryModalOpen" x-trap.inert.noscroll="directoryModalOpen"
                                        x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-2 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 -translate-y-2 sm:scale-95"
                                        class="application-settings-form application-settings-section relative w-full min-w-full lg:min-w-[36rem] lg:max-w-2xl">
                                        <header>
                                            <h3>{{ __('common.directory_mount') }}</h3>
                                            <button @click="directoryModalOpen=false"
                                                class="flex size-7 items-center justify-center rounded-md text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.07] dark:hover:text-fg">
                                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </header>
                                        <div class="application-settings-section-body relative flex items-center justify-center w-auto"
                                            x-init="$watch('directoryModalOpen', value => {
                                                if (value) {
                                                    $nextTick(() => {
                                                        const input = $el.querySelector('input');
                                                        input?.focus();
                                                    })
                                                }
                                            })">
                                            <form class="flex w-full flex-col gap-4"
                                                wire:submit='submitFileStorageDirectory'>
                                                <p class="text-[13px] leading-5 text-neutral-500 dark:text-fg-dim">
                                                    {{ __('common.bind_host_directory') }}
                                                </p>
                                                <div class="flex flex-col gap-4">
                                                    <x-forms.input canGate="update" :canResource="$resource"
                                                        placeholder="{{ application_configuration_dir() }}/{{ $resource->uuid }}/etc/nginx"
                                                        id="file_storage_directory_source" :label="__('common.source_directory')"
                                                        required :helper="__('common.directory_on_host')" />
                                                    <x-forms.input canGate="update" :canResource="$resource"
                                                        placeholder="/etc/nginx" id="file_storage_directory_destination"
                                                        :label="__('common.destination_directory')" required
                                                        :helper="__('common.directory_inside_container')" />
                                                    <div class="flex justify-end pt-2">
                                                        <x-forms.button canGate="update" :canResource="$resource" type="submit">
                                                            {{ __('common.add_directory') }}
                                                        </x-forms.button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endcan
                @endif
                @if ($hasVolumes || $hasFiles || $hasDirectories)
                    <div
                        class="inline-flex items-center gap-0.5 rounded-lg bg-neutral-100 p-1 dark:bg-white/[0.04]">
                        <button type="button" wire:click="setActiveTab('volumes')"
                            @disabled(!$hasVolumes)
                            @class([$tabButtonBase, $activeTab === 'volumes' ? $tabButtonActive : $tabButtonInactive])>
                            {{ __('common.volume') }} ({{ $this->volumeCount }})
                        </button>
                        <button type="button" wire:click="setActiveTab('files')"
                            @disabled(!$hasFiles)
                            @class([$tabButtonBase, $activeTab === 'files' ? $tabButtonActive : $tabButtonInactive])>
                            {{ __('common.file') }} ({{ $this->fileCount }})
                        </button>
                        <button type="button" wire:click="setActiveTab('directories')"
                            @disabled(!$hasDirectories)
                            @class([$tabButtonBase, $activeTab === 'directories' ? $tabButtonActive : $tabButtonInactive])>
                            {{ __('common.directory') }} ({{ $this->directoryCount }})
                        </button>
                    </div>
                @endif
            </x-slot:actions>

        @if (!$hasVolumes && !$hasFiles && !$hasDirectories)
            <x-empty size="sm" :title="__('common.no_persistent_storage')"
                :description="__('common.storage_preserve_data')"
                icon-name="storages" />
        @elseif ($activeTab === 'volumes')
            @if ($hasVolumes)
                <livewire:project.shared.storages.all wire:key="volumes-{{ $resource->id }}"
                    :resource="$resource" />
            @else
                <x-empty size="sm" :title="__('common.no_volumes_configured')"
                    :description="__('common.switch_tabs_add_volume')" icon-name="storages" />
            @endif
        @elseif ($activeTab === 'files')
            <div class="flex flex-col gap-4 p-4">
                @if ($hasFiles)
                    @foreach ($this->files as $fs)
                        <livewire:project.service.file-storage :fileStorage="$fs"
                            wire:key="file-{{ $fs->id }}" />
                    @endforeach
                @else
                    <x-empty size="sm" :title="__('common.no_file_mounts_configured')"
                        :description="__('common.switch_tabs_add_file')" icon-name="file" />
                @endif
            </div>
        @else
            <div class="flex flex-col gap-4 p-4">
                @if ($hasDirectories)
                    @foreach ($this->directories as $fs)
                        <livewire:project.service.file-storage :fileStorage="$fs"
                            wire:key="directory-{{ $fs->id }}" />
                    @endforeach
                @else
                    <x-empty size="sm" :title="__('common.no_directory_mounts_configured')"
                        :description="__('common.switch_tabs_add_directory')" icon-name="folder" />
                @endif
            </div>
        @endif
        </x-application.settings-section>
    @else
        {{-- Service stack resources: one settings card + table per service --}}
        <x-application.settings-section :id="'storage-service-'.$resource->uuid"
            :title="Str::headline($resource->name)" :flush="true"
            :helper="__('common.volume_mounts_compose_readonly')">
            <x-slot:actions>
                @if ($hasVolumes || $hasFiles || $hasDirectories)
                    <div
                        class="inline-flex items-center gap-0.5 rounded-lg bg-neutral-100 p-1 dark:bg-white/[0.04]">
                        <button type="button" wire:click="setActiveTab('volumes')"
                            @disabled(!$hasVolumes)
                            @class([$tabButtonBase, $activeTab === 'volumes' ? $tabButtonActive : $tabButtonInactive])>
                            {{ __('common.volume') }} ({{ $this->volumeCount }})
                        </button>
                        <button type="button" wire:click="setActiveTab('files')"
                            @disabled(!$hasFiles)
                            @class([$tabButtonBase, $activeTab === 'files' ? $tabButtonActive : $tabButtonInactive])>
                            {{ __('common.file') }} ({{ $this->fileCount }})
                        </button>
                        <button type="button" wire:click="setActiveTab('directories')"
                            @disabled(!$hasDirectories)
                            @class([$tabButtonBase, $activeTab === 'directories' ? $tabButtonActive : $tabButtonInactive])>
                            {{ __('common.directory') }} ({{ $this->directoryCount }})
                        </button>
                    </div>
                @endif
            </x-slot:actions>

            @if (!$hasVolumes && !$hasFiles && !$hasDirectories)
                <x-empty size="sm" :title="__('common.no_storage_found')"
                    :description="__('common.service_storage_not_defined')"
                    icon-name="storages" />
            @elseif ($activeTab === 'volumes')
                @if ($hasVolumes)
                    <livewire:project.shared.storages.all
                        wire:key="svc-volumes-{{ $resource->id }}"
                        :resource="$resource" />
                @else
                    <x-empty size="sm" :title="__('common.no_volumes_configured')"
                        :description="__('common.service_no_volume_mounts')" icon-name="storages" />
                @endif
            @elseif ($activeTab === 'files')
                <div class="flex flex-col gap-4 p-4">
                    @if ($hasFiles)
                        @foreach ($this->files as $fs)
                            <livewire:project.service.file-storage :fileStorage="$fs"
                                wire:key="file-{{ $fs->id }}" />
                        @endforeach
                    @else
                        <x-empty size="sm" :title="__('common.no_file_mounts_configured')"
                            :description="__('common.service_no_file_mounts')" icon-name="file" />
                    @endif
                </div>
            @else
                <div class="flex flex-col gap-4 p-4">
                    @if ($hasDirectories)
                        @foreach ($this->directories as $fs)
                            <livewire:project.service.file-storage :fileStorage="$fs"
                                wire:key="directory-{{ $fs->id }}" />
                        @endforeach
                    @else
                        <x-empty size="sm" :title="__('common.no_directory_mounts_configured')"
                            :description="__('common.service_no_directory_mounts')" icon-name="folder" />
                    @endif
                </div>
            @endif
        </x-application.settings-section>
    @endif
</div>
