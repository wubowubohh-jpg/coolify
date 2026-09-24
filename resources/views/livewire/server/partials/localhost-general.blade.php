                <form wire:submit.prevent="submit" class="application-settings-form flex flex-col gap-6">
                    <x-unsaved-bar action="submit" />
                    <x-application.settings-section id="server-overview-section" :title="__('common.server_overview')"
                        :helper="__('common.server_overview_localhost_helper')">
                        <x-slot:actions>
                            @if ($server->server_metadata)
                                <x-forms.button type="button" class="size-8! px-0!"
                                    wire:click="refreshServerMetadata" :title="__('common.refresh_server_details')">
                                    <x-reicon name="refresh" class="size-3.5" />
                                </x-forms.button>
                            @endif
                            <x-status-badge :status="$server->isFunctional() ? __('common.ready') : __('common.validation_required')"
                                :type="$server->isFunctional() ? 'success' : 'warning'" />
                        </x-slot:actions>

                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-neutral-600 dark:bg-white/[0.06] dark:text-fg-dim">
                                <x-reicon name="servers" class="size-4.5" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-neutral-950 dark:text-fg">{{ __('common.localhost') }}</p>
                                <p class="mt-1 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                                    @if ($server->isFunctional())
                                        {{ __('common.server_ready_description') }}
                                    @else
                                        {{ __('common.local_docker_validation_description') }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if ($server->server_metadata)
                            @include('livewire.server.partials.server-details', ['server' => $server])
                        @else
                            <div class="mt-4 border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                                <x-forms.button type="button" wire:click="refreshServerMetadata">
                                    <x-reicon name="refresh" class="size-3.5" />
                                    {{ __('common.fetch_server_details') }}
                                </x-forms.button>
                            </div>
                        @endif
                    </x-application.settings-section>

                    @if ($server->validation_logs)
                        <x-application.settings-section :title="__('common.previous_validation_output')"
                            :helper="__('common.previous_validation_output_helper')">
                            <div
                                class="max-h-72 overflow-auto rounded-lg bg-neutral-950 p-4 font-mono text-xs leading-5 text-neutral-300">
                                {!! $server->validation_logs !!}
                            </div>
                        </x-application.settings-section>
                    @endif

                    <x-application.settings-section id="server-connection-section" :title="__('common.connection')"
                        :helper="__('common.server_connection_helper')">
                        <x-slot:actions>
                            <x-forms.button type="button" wire:click.prevent="checkLocalhostConnection"
                                canGate="update" :canResource="$server">
                                <x-reicon name="refresh" class="size-3.5" />
                                {{ __('common.validate_connection') }}
                            </x-forms.button>
                        </x-slot:actions>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.input canGate="update" :canResource="$server" id="name" :label="__('common.name')"
                                required :disabled="$isValidating" />
                            <x-forms.input canGate="update" :canResource="$server" id="description"
                                :label="__('common.description')" :disabled="$isValidating" />
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-3">
                            <x-forms.input canGate="update" :canResource="$server" type="password" id="ip"
                                :label="__('common.ip_address_or_domain')"
                                :helper="__('common.ip_address_or_domain_helper')"
                                required :disabled="$isValidating" />
                            <x-forms.input canGate="update" :canResource="$server" id="user" :label="__('common.ssh_user')"
                                required :disabled="$isValidating" />
                            <x-forms.input canGate="update" :canResource="$server" type="number" id="port"
                                :label="__('common.ssh_port')" required :disabled="$isValidating" />
                        </div>

                        <div class="mt-4 grid gap-4 lg:grid-cols-3">
                            <x-forms.input canGate="update" :canResource="$server" type="number"
                                id="connectionTimeout" :label="__('common.connection_timeout')"
                                :helper="__('common.connection_timeout_helper')" min="1" max="300"
                                required :disabled="$isValidating" />
                            <x-forms.searchable-listbox id="serverTimezone" :label="__('common.server_timezone')"
                                :helper="__('common.server_timezone_helper')"
                                :searchPlaceholder="__('settings.search_timezones')"
                                :emptyText="__('settings.no_matching_timezone')"
                                :options="collect($this->timezones)->map(fn ($timezone) => [
                                    'value' => $timezone,
                                    'label' => $timezone,
                                ])->all()" :disabled="$isValidating || !auth()->user()->can('update', $server)" />
                            <x-forms.input canGate="update" :canResource="$server"
                                placeholder="https://example.com" id="wildcardDomain"
                                :label="__('common.wildcard_domain')"
                                :helper="__('common.wildcard_domain_helper')"
                                :disabled="$isValidating" />
                        </div>
                    </x-application.settings-section>
                </form>
