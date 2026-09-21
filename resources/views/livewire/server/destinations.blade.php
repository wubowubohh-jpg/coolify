<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > Destinations | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="destinations" />

        <div class="application-settings-form flex w-full flex-col gap-6">
            @if ($server->isFunctional())
                <x-application.settings-section id="server-destinations-section" title="Destinations"
                    helper="Docker networks used to isolate and connect resources on this server." flush>
                    <x-slot:actions>
                        <div class="flex items-center gap-2">
                            <x-forms.button canGate="update" :canResource="$server" wire:click="scan">
                                <x-reicon name="refresh" class="size-3.5" />
                                {{ __('common.scan_networks') }}
                            </x-forms.button>
                            @can('update', $server)
                                <x-modal-input :buttonTitle="__('common.add')" :title="__('common.new_destination')">
                                    <livewire:destination.new.docker :server_id="$server->id" />
                                </x-modal-input>
                            @endcan
                        </div>
                    </x-slot:actions>

                    @forelse ($server->standaloneDockers->concat($server->swarmDockers) as $destination)
                        <a href="{{ route('destination.show', ['destination_uuid' => data_get($destination, 'uuid')]) }}"
                            {{ wireNavigate() }}
                            class="flex items-center gap-4 border-b border-neutral-200 px-4 py-3 transition-colors last:border-b-0 hover:bg-neutral-50 dark:border-white/[0.08] dark:hover:bg-white/[0.03]">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-neutral-500 dark:bg-white/[0.06] dark:text-fg-dim">
                                <x-reicon name="destinations" class="size-4" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-neutral-950 dark:text-fg">
                                    {{ data_get($destination, 'network') }}
                                </p>
                                <p class="mt-0.5 text-xs text-neutral-500 dark:text-fg-dim">
                                    {{ $server->swarmDockers->contains('id', data_get($destination, 'id')) ? __('common.docker_swarm') : __('common.standalone_docker') }}
                                </p>
                            </div>
                        </a>
                    @empty
                        <x-empty size="sm" :title="__('common.no_destinations_yet')"
                            :description="__('common.add_or_scan_destinations')"
                            icon-name="destinations" />
                    @endforelse
                </x-application.settings-section>

                @if ($networks->count() > 0)
                    <x-application.settings-section id="server-found-networks-section" :title="__('common.discovered_networks')"
                        :helper="__('common.discovered_networks_description')"
                        flush>
                        @foreach ($networks as $network)
                            <div
                                class="flex items-center justify-between gap-4 border-b border-neutral-200 px-4 py-3 last:border-b-0 dark:border-white/[0.08]">
                                <div>
                                    <p class="text-sm font-medium text-neutral-950 dark:text-fg">
                                        {{ data_get($network, 'Name') }}
                                    </p>
                                    <p class="mt-0.5 text-xs text-neutral-500 dark:text-fg-dim">{{ __('common.docker_network') }}</p>
                                </div>
                                <x-forms.button canGate="update" :canResource="$server"
                                    wire:click="add('{{ data_get($network, 'Name') }}')">
                                    {{ __('common.add_destination') }}
                                </x-forms.button>
                            </div>
                        @endforeach
                    </x-application.settings-section>
                @endif
            @else
                <x-application.settings-section :title="__('common.destinations')"
                    :helper="__('common.server_destinations_description')">
                    <x-empty size="sm" :title="__('common.server_validation_required')"
                        :description="__('common.validate_server_before_destinations')"
                        icon-name="destinations" />
                </x-application.settings-section>
            @endif
        </div>
    </div>
</div>
