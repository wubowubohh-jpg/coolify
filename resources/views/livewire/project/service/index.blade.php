<div>
    @unless ($embedded)
        <livewire:project.service.heading :service="$service" :parameters="$parameters" :query="$query" />
    @endunless
    <section @class(['application-settings-workspace mt-4 w-full max-w-none lg:mt-0' => ! $embedded])>
        <div @class(['grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8' => ! $embedded])>
        @if (! $embedded && $resourceType === 'database')
            <x-service-database.sidebar :parameters="$parameters" :serviceDatabase="$serviceDatabase" />
        @elseif (! $embedded)
            <aside class="application-settings-navigation min-w-0 xl:self-start">
                <nav :aria-label="__('common.compose_resource_settings')"
                    class="grid grid-cols-2 gap-0.5 border-y border-neutral-200 py-3 sm:grid-cols-3 xl:grid-cols-1 xl:border-y-0 xl:py-0 dark:border-white/[0.06]">
                    <div class="nav-section hidden xl:block">{{ __('common.compose_resource') }}</div>
                <a class="menu-item" {{ wireNavigate() }}
                    href="{{ route('project.service.configuration', [...$parameters, 'stack_service_uuid' => null]) }}">
                    <x-reicon name="logout" class="menu-item-icon rotate-180" />
                    <span class="menu-item-label">{{ __('common.back_to_service') }}</span>
                </a>
                <a @class(['menu-item', 'menu-item-active' => request()->routeIs('project.service.index')])
                    {{ wireNavigate() }} href="{{ route('project.service.index', $parameters) }}">
                    <x-reicon name="settings" class="menu-item-icon" />
                    <span class="menu-item-label">{{ __('common.general') }}</span>
                </a>
                </nav>
            </aside>
        @endif
        <div class="min-w-0">
            @if ($resourceType === 'application')
                @unless ($embedded)
                    <x-slot:title>
                        {{ data_get_str($service, 'name')->limit(10) }} >
                        {{ data_get_str($serviceApplication, 'name')->limit(10) }} | Coolify
                    </x-slot>
                @endunless
                    <form wire:submit="submitApplication" class="space-y-6">
                        <div class="space-y-4">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-forms.input canGate="update" :canResource="$serviceApplication" label="Name" id="humanName"
                                    placeholder="Human readable name"></x-forms.input>
                                <x-forms.input canGate="update" :canResource="$serviceApplication" label="Description"
                                    id="description"></x-forms.input>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                @if (!$serviceApplication->serviceType()?->contains(str($serviceApplication->image)->before(':')))
                                    <div data-domain-summary
                                        class="rounded-lg border border-neutral-200 p-4 dark:border-white/[0.08]">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <p class="text-sm text-neutral-500 dark:text-fg-dim">
                                                @php($domainCount = countDomains($fqdn))
                                                @if ($domainCount === 0)
                                                    {{ __('common.no_domains_set') }}
                                                @elseif ($domainCount === 1)
                                                    {{ __('common.one_domain_set') }}
                                                @else
                                                    {{ __('common.domains_set', ['count' => $domainCount]) }}
                                                @endif
                                            </p>
                                            <a class="button shrink-0" href="{{ route('project.service.domains', $parameters) }}"
                                                {{ wireNavigate() }}>
                                                <x-reicon name="globe" class="size-4" />
                                                {{ __('common.manage_domains') }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                                <x-forms.input canGate="update" :canResource="$serviceApplication"
                                    :helper="__('common.change_deploy_image_warning')"
                                    :label="__('common.image')" id="image"></x-forms.input>
                            </div>
                        </div>

                        @include('livewire.project.service.advanced-settings')

                        <div data-service-resource-actions
                            class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-200 pt-5 dark:border-white/[0.08]">
                            <div>
                                @can('delete', $serviceApplication)
                                    <x-modal-confirmation :title="__('common.confirm_service_application_deletion')" :buttonTitle="__('common.delete')"
                                        isErrorButton submitAction="deleteApplication"
                                        :actions="[__('common.service_application_delete_action')]"
                                        confirmationText="{{ Str::headline($serviceApplication->name) }}"
                                        :confirmationLabel="__('common.confirmation_label').' '.__('common.service_application_name')"
                                        :shortConfirmationLabel="__('common.service_application_name')" />
                                @endcan
                            </div>
                            <div class="ml-auto flex items-center gap-2">
                                @can('update', $serviceApplication)
                                    <x-modal-confirmation wire:click="convertToDatabase" :title="__('common.convert_to_database')"
                                        :buttonTitle="__('common.convert_to_database')" submitAction="convertToDatabase"
                                        :actions="[__('common.convert_to_database_action')]"
                                        confirmationText="{{ Str::headline($serviceApplication->name) }}"
                                        :confirmationLabel="__('common.confirmation_label').' '.__('common.service_application_name')"
                                        :shortConfirmationLabel="__('common.service_application_name')" />
                                    <x-forms.button type="submit" isHighlighted>{{ __('common.save_changes') }}</x-forms.button>
                                @endcan
                            </div>
                        </div>
                    </form>

                    <x-domain-conflict-modal
                        :conflicts="$domainConflicts"
                        :showModal="$showDomainConflictModal"
                        confirmAction="confirmDomainUsage">
                        <x-slot:consequences>
                            <ul class="mt-2 ml-4 list-disc">
                                <li>{{ __('common.only_one_resource_accessible') }}</li>
                                <li>{{ __('common.routing_unpredictable') }}</li>
                                <li>{{ __('common.service_disruptions') }}</li>
                                <li>{{ __('common.ssl_certificates_might_fail') }}</li>
                            </ul>
                        </x-slot:consequences>
                    </x-domain-conflict-modal>

                    @if ($showPortWarningModal)
                        <div x-data="{ modalOpen: true }" x-init="$nextTick(() => { modalOpen = true })"
                            @keydown.escape.window="modalOpen = false; $wire.call('cancelRemovePort')"
                            :class="{ 'z-40': modalOpen }" class="relative">
                            <template x-teleport="body">
                                <div x-show="modalOpen"
                                    class="fixed inset-0 z-99 flex min-h-full items-center justify-center overflow-y-auto p-4" x-cloak>
                                    <div x-show="modalOpen" class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>
                                    <div x-show="modalOpen" x-trap.inert.noscroll="modalOpen" x-transition:enter="ease-out duration-100"
                                        x-transition:enter-start="opacity-0 -translate-y-2 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-100"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 -translate-y-2 sm:scale-95"
                                        class="application-settings-form application-settings-section relative w-full lg:min-w-[36rem] lg:max-w-2xl"
                                        style="box-shadow: 0 0 0 1px var(--coollabs-hairline), var(--shadow-modal)">
                                        <header>
                                            <h3>{{ __('common.use_different_port') }}</h3>
                                            <button @click="modalOpen = false; $wire.call('cancelRemovePort')"
                                                class="flex size-7 items-center justify-center rounded-md text-neutral-500 transition-colors hover:bg-neutral-100 hover:text-black dark:text-fg-faint dark:hover:bg-white/[0.06] dark:hover:text-fg">
                                                <x-reicon name="x" class="size-4" />
                                            </button>
                                        </header>
                                        <div class="application-settings-section-body">
                                            <x-callout type="warning" :title="__('common.port_requirement')" class="mb-4">
                                                {{ __('common.service_port_requirement_description', ['port' => $requiredPort]) }}
                                                {{ __('common.domains_use_different_port_or_none') }}
                                            </x-callout>

                                            <x-callout type="danger" :title="__('common.what_happen_continue')" class="mb-4">
                                                <ul class="mt-2 ml-4 list-disc">
                                                    <li>{{ __('common.service_may_become_unreachable') }}</li>
                                                    <li>{{ __('common.proxy_may_not_route') }}</li>
                                                    <li>{{ __('common.environment_variables_not_generated') }}</li>
                                                    <li>{{ __('common.service_may_fail_start') }}</li>
                                                </ul>
                                            </x-callout>

                                            <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                                                <x-forms.button @click="modalOpen = false; $wire.call('cancelRemovePort')"
                                                    class="w-auto">
                                                    {{ __('common.keep_required_service_port') }}
                                                </x-forms.button>
                                                <x-forms.button wire:click="confirmRemovePort" @click="modalOpen = false" class="w-auto"
                                                    isError>
                                                    {{ __('common.use_this_port_anyway') }}
                                                </x-forms.button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    @endif
            @elseif ($resourceType === 'database')
                @unless ($embedded)
                    <x-slot:title>
                        {{ data_get_str($service, 'name')->limit(10) }} >
                        {{ data_get_str($serviceDatabase, 'name')->limit(10) }} | Coolify
                    </x-slot>
                @endunless
                @if ($currentRoute === 'project.service.database.import')
                    <livewire:project.database.import :resource="$serviceDatabase" :key="'import-' . $serviceDatabase->uuid" />
                @else
                    <form wire:submit="submitDatabase" class="space-y-6">
                        <div class="space-y-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <x-forms.input canGate="update" :canResource="$serviceDatabase" :label="__('common.name')" id="humanName"
                                    :placeholder="__('common.name')"></x-forms.input>
                                <x-forms.input canGate="update" :canResource="$serviceDatabase" :label="__('common.description')"
                                    id="description"></x-forms.input>
                                <x-forms.input class="sm:col-span-2" canGate="update" :canResource="$serviceDatabase" required
                                    :helper="__('common.change_deploy_image_warning')"
                                    :label="__('common.image')" id="image"></x-forms.input>
                            </div>
                            <div class="border-t border-neutral-200 pt-5 dark:border-white/[0.06]">
                                <div class="mb-4 flex items-center justify-between gap-2">
                                    <h3 class="text-sm font-semibold text-black dark:text-fg">{{ __('common.public_access') }}</h3>
                                    <div class="flex items-center gap-2">
                                        <x-loading wire:loading wire:target="instantSave" />
                                        @if ($serviceDatabase->is_public)
                                            <x-process-dialog closeWithX size="xl">
                                                <x-slot:title>{{ __('common.proxy_logs') }}</x-slot:title>
                                                <x-slot:content>
                                                    <livewire:project.shared.get-logs :server="$server" :resource="$service"
                                                        :servicesubtype="$serviceDatabase" container="{{ $serviceDatabase->uuid }}-proxy" :collapsible="false" lazy />
                                                </x-slot:content>
                                                <x-forms.button @click="processDialogOpen = true">{{ __('common.logs') }}</x-forms.button>
                                            </x-process-dialog>
                                        @endif
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end"
                                        x-data="{ port: @js(filled($publicPort) ? (string) $publicPort : '') }"
                                        @input="if ($event.target.matches('input[type=number], input:not([type])')) port = $event.target.value">
                                        <div class="w-full sm:max-w-xs">
                                            <x-forms.input type="number" canGate="update" :canResource="$serviceDatabase"
                                                placeholder="5432" disabled="{{ $isPublic }}" id="publicPort"
                                                :label="__('common.public_port')" />
                                        </div>
                                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                                            @if ($isPublic)
                                                <x-status-badge :status="__('common.public')" type="success" />
                                                <x-forms.button canGate="update" :canResource="$serviceDatabase"
                                                    wire:click="disablePublicAccess">
                                                    {{ __('common.make_private') }}
                                                </x-forms.button>
                                            @else
                                                {{-- Do not nest @if/@endif inside an <x-*> opening tag: Blade component
                                                     compilation breaks and yields "unexpected token endif". --}}
                                                <x-forms.button canGate="update" :canResource="$serviceDatabase"
                                                    wire:click="enablePublicAccess"
                                                    x-bind:disabled="!String(port ?? '').trim()">
                                                    {{ __('common.make_public') }}
                                                </x-forms.button>
                                            @endif
                                        </div>
                                    </div>
                                    @if ($db_url_public)
                                        <x-forms.input :label="__('common.database_ip_port_public')"
                                            :helper="__('common.credentials_in_environment')" type="password"
                                            readonly wire:model="db_url_public" />
                                    @endif
                                </div>
                            </div>
                        </div>

                        @include('livewire.project.service.advanced-settings')

                        <div data-service-resource-actions
                            class="flex flex-wrap items-center justify-between gap-3 border-t border-neutral-200 pt-5 dark:border-white/[0.08]">
                            <div>
                                @can('delete', $serviceDatabase)
                                    <x-modal-confirmation :title="__('common.confirm_service_database_deletion')" :buttonTitle="__('common.delete')"
                                        isErrorButton submitAction="deleteDatabase" :actions="[
                                            __('common.service_database_delete_action'),
                                        ]"
                                        confirmationText="{{ Str::headline($serviceDatabase->name) }}"
                                        :confirmationLabel="__('common.confirmation_label').' '.__('common.service_database_name')"
                                        :shortConfirmationLabel="__('common.service_database_name')" />
                                @endcan
                            </div>
                            <div class="ml-auto flex items-center gap-2">
                                @can('update', $serviceDatabase)
                                    <x-modal-confirmation wire:click="convertToApplication" :title="__('common.convert_to_application')"
                                        :buttonTitle="__('common.convert_to_application')" submitAction="convertToApplication"
                                        :actions="[__('common.convert_to_application_action')]"
                                        confirmationText="{{ Str::headline($serviceDatabase->name) }}"
                                        :confirmationLabel="__('common.confirmation_label').' '.__('common.service_database_name')"
                                        :shortConfirmationLabel="__('common.service_database_name')" />
                                    <x-forms.button type="submit" isHighlighted>{{ __('common.save_changes') }}</x-forms.button>
                                @endcan
                            </div>
                        </div>
                    </form>
                @endif
            @endif
        </div>
        </div>
    </section>
</div>
