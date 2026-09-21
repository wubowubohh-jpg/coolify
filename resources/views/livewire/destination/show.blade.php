<div>
    <x-slot:title>
        {{ $destination->name }} | Destination | Coolify
    </x-slot>

    @php
        $destinationSubtitle = $destination->getMorphClass() === 'App\Models\StandaloneDocker'
            ? __('common.docker_network_on_server', ['server' => data_get($destination, 'server.name', __('common.server'))])
            : __('common.deprecated_docker_swarm_network');
    @endphp

    <x-dashboard.navbar section="destination" :parameters="['destination_uuid' => $destination->uuid]"
        :title="$name" :subtitle="$destinationSubtitle" :mobileTitleOnly="true" />

    <section class="application-settings-workspace mt-4 w-full max-w-none lg:mt-0">
        <div class="grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
            @include('livewire.destination.sidebar', ['destination' => $destination])

            <div class="min-w-0">
                @if (request()->routeIs('destination.danger'))
                    <div class="application-settings-form">
                        <x-application.settings-section id="destination-danger-section" :title="__('common.danger_zone')"
                            :helper="__('common.destination_delete_irreversible')">
                            <x-danger-zone :title="__('common.delete_destination')">
                                <p>
                                    {{ __('common.permanently_delete') }} <strong class="font-semibold">{{ $destination->name }}</strong>
                                    {{ __('common.destination_delete_description') }}
                                </p>
                                <p>{{ __('common.delete_attached_resources') }}</p>
                                <x-slot:action>
                                    @if ($network !== 'coolify')
                                        <x-modal-confirmation :title="__('common.confirm_destination_deletion')"
                                            :buttonTitle="__('common.delete_destination')" isErrorButton submitAction="delete"
                                            :actions="[__('common.destination_delete_confirmation_action')]"
                                            confirmationText="{{ $destination->name }}"
                                            :confirmationLabel="__('common.enter_destination_name')"
                                            :shortConfirmationLabel="__('common.destination_name')" :confirmWithPassword="false"
                                            :step2ButtonText="__('common.permanently_delete')" canGate="delete"
                                            :canResource="$destination" />
                                    @else
                                        <x-forms.button isError disabled :tooltip="__('common.default_destination_cannot_delete')">
                                            {{ __('common.delete_destination') }}
                                        </x-forms.button>
                                    @endif
                                </x-slot:action>
                            </x-danger-zone>
                        </x-application.settings-section>
                    </div>
                @else
                    <form wire:submit="submit" class="application-settings-form">
                    <x-unsaved-bar action="submit" />

                    <x-application.settings-section :title="__('common.general')"
                        :description="$destination->getMorphClass() === 'App\Models\StandaloneDocker'
                            ? __('common.docker_network_connect_resources')
                            : __('common.deprecated_docker_swarm_network')">
                    @if ($destination->getMorphClass() !== 'App\Models\StandaloneDocker')
                        <x-slot:actions>
                            <x-status-badge :label="__('common.deprecated')" type="warning" />
                        </x-slot:actions>
                    @endif

                        <div class="grid gap-4 lg:grid-cols-2">
                            <x-forms.input canGate="update" :canResource="$destination" id="name" :label="__('common.name')" />
                            <x-forms.input id="serverIp" :label="__('common.server_ip')" readonly />
                            @if ($destination->getMorphClass() === 'App\Models\StandaloneDocker')
                                <div class="lg:col-span-2">
                                    <x-forms.input id="network" :label="__('common.docker_network')" readonly />
                                </div>
                            @endif
                        </div>
                    </x-application.settings-section>
                    </form>
                @endif
            </div>
        </div>
    </section>
</div>
