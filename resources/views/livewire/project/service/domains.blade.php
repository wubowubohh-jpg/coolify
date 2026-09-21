@php
    $configuredCount = collect($domainRows)->where('is_suggested', false)->count();
    $suggestedCount = collect($domainRows)->where('is_suggested', true)->count();
    $hasRows = count($domainRows) > 0;
    $hasDnsChecksInProgress = collect($domainRows)->contains(fn ($row) => $row['dns_status'] === 'checking');
    $serviceAppCount = count($serviceApps);
    $domainGroups = collect($domainRows)
        ->groupBy('service_application_id')
        ->filter(fn ($rows) => $rows->contains(fn ($row) => ! ($row['is_suggested'] ?? false)));
    $domainSearchValues = $domainGroups->map(function ($rows, $appId) use ($serviceApps) {
        $app = collect($serviceApps)->firstWhere('id', (int) $appId);
        $heading = \Illuminate\Support\Str::headline($app['name'] ?? $rows->first()['service_name'] ?? 'Service');

        return $heading.' '.$rows->pluck('url')->implode(' ');
    })->values();
@endphp

<div id="service-domains-section" class="flex flex-col gap-4"
    x-data="{
        domainSearch: '',
        modalOpen: @js($showEditDomainModal || $editDomainDnsFailed),
        editingServiceLabel: '',
        editingDomainBaseline: null,
        get hasAddressChanges() {
            return this.modalOpen && this.editingDomainBaseline !== null
                && JSON.stringify($wire.editingDomainParts) !== this.editingDomainBaseline
                && !$wire.showPortWarningModal && !$wire.showDomainConflictModal;
        },
        openEditDomain(index, domain, parts, serviceApplicationId, serviceLabel, indexing, redirect) {
            if (index !== undefined) {
                $wire.set('editingIndex', index, false);
                $wire.set('editingDomain', domain, false);
                $wire.set('editingDomainParts', parts, false);
                $wire.set('editingDomainPartsChanged', false, false);
                $wire.set('editingServiceApplicationId', serviceApplicationId, false);
                $wire.set('editingIndexing', indexing, false);
                $wire.set('editingRedirect', redirect, false);
                $wire.set('editingOriginalRedirect', redirect, false);
                $wire.set('editingDomainWasRegenerated', false, false);
                $wire.set('editingGeneratedHost', null, false);
            }
            this.editingDomainBaseline = JSON.stringify($wire.editingDomainParts);
            this.editingServiceLabel = serviceLabel ?? $wire.serviceApps.find(app => app.id === $wire.editingServiceApplicationId)?.name ?? '';
            this.modalOpen = true;
            this.$nextTick(() => document.getElementById('editingDomainParts-host')?.focus?.());
        },
        closeEditDomain(discardDraft = true) {
            this.modalOpen = false;
            this.editingDomainBaseline = null;
            this.editingServiceLabel = '';
            if (discardDraft) this.$wire.cancelEdit();
        },
        matchesDomainSearch(value) {
            return !this.domainSearch.trim() || value.toLowerCase().includes(this.domainSearch.trim().toLowerCase());
        },
        hasDomainSearchResults(values) {
            return values.some((value) => this.matchesDomainSearch(value));
        },
    }"
    @open-edit-domain.window="openEditDomain()"
    @edit-domain-saved.window="closeEditDomain(false)">
    @if ($hasDnsChecksInProgress)
        <div class="hidden" wire:poll.2000ms="pollDnsChecks" aria-hidden="true"></div>
    @endif
    @cannot('update', $service)
        <x-callout type="danger" :title="__('common.insufficient_permissions')">
            {{ __('common.manage_domains_permission') }}
        </x-callout>
    @endcannot

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2">
        <div class="min-w-0 flex-1">
            <h2 id="domains-section">{{ __('common.domains') }}</h2>
            <p class="text-[13px] text-neutral-500 dark:text-fg-dim">
                {{ $configuredCount }} domain{{ $configuredCount === 1 ? '' : 's' }} across {{ $domainGroups->count() }} service{{ $domainGroups->count() === 1 ? '' : 's' }}
                @if ($suggestedCount > 0)
                    · {{ $suggestedCount }} not added
                @endif
            </p>
        </div>
        <div class="ml-auto flex flex-wrap items-center gap-2">
            @if ($domainGroups->isNotEmpty())
                <div class="relative w-full sm:w-64">
                    <x-reicon name="search"
                        class="pointer-events-none absolute top-1/2 left-2.5 z-10 size-3.5 -translate-y-1/2 text-neutral-400 dark:text-fg-faint" />
                    <input type="search" x-model="domainSearch" :aria-label="__('common.search_services_domains')"
                        class="input h-8! w-full pl-8! text-[13px]!" :placeholder="__('common.search_services_domains')" />
                </div>
            @endif
            @can('update', $service)
                @if ($configuredCount > 0)
                    <x-forms.button wire:click="checkAllDns" :showLoadingIndicator="false" wire:loading.attr="disabled"
                        wire:target="checkAllDns,checkDomainDns">
                        <x-reicon name="refresh" class="size-3.5" />
                        {{ __('common.check_all_dns') }}
                    </x-forms.button>
                @endif
                @if ($serviceAppCount > 0)
                    <div class="relative shrink-0">
                        @include('livewire.project.shared.cloudflare-autoconfigure')
                    </div>
                    <x-modal-input :title="__('common.add_domain')" :closeOutside="false" :wireIgnore="false"
                        canGate="update" :canResource="$service">
                        <x-slot:content>
                            <button type="button"
                                class="button button-highlighted">
                                <x-reicon name="plus" class="size-3.5" />
                                {{ __('common.add_domain') }}
                            </button>
                        </x-slot:content>
                        <form wire:submit="addDomain" class="application-settings-form flex flex-col gap-4">
                            {{-- Always show which service receives the domain --}}
                            <x-forms.listbox canGate="update" :canResource="$service" :label="__('common.service_application')" id="newServiceApplicationId" required portal
                                :helper="__('common.domain_assigned_to_service_application')"
                                :options="collect($serviceApps)->map(fn ($app) => [
                                    'value' => $app['id'],
                                    'label' => $app['name'].(filled($app['image'] ?? null) ? ' ('.$app['image'].')' : ''),
                                ])->values()->all()"
                                :disabled="! auth()->user()->can('update', $service)" />

                            <x-forms.domain-input id="newDomainParts" errorId="newDomain" />

                            @if ($addDomainDnsFailed)
                                <x-callout type="danger" :title="__('common.dns_not_pointing_to_ip')">
                                    {{ __('common.dns_domain_not_resolved') }}
                                    {{ __('common.dns_traffic_warning') }}
                                    {{ __('common.dns_add_anyway_question') }}
                                    @if (filled($addDomainDnsMessage))
                                        <div class="pt-2">{{ $addDomainDnsMessage }}</div>
                                    @endif
                                </x-callout>
                            @endif

                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2">
                                <x-forms.button canGate="update" :canResource="$service" type="button"
                                    wire:click="generateDomain">
                                    {{ __('common.generate_domain') }}
                                </x-forms.button>
                                <div class="flex flex-wrap gap-2">
                                    @if ($addDomainDnsFailed)
                                        <x-forms.button canGate="update" :canResource="$service" type="button"
                                            wire:click="confirmAddDomainDespiteDns" isError>
                                            {{ __('common.continue') }}
                                        </x-forms.button>
                                    @else
                                        <x-forms.button canGate="update" :canResource="$service" type="submit"
                                            isHighlighted>
                                            {{ __('common.save') }}
                                        </x-forms.button>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </x-modal-input>
                @endif
            @endcan
        </div>
    </div>

    @if ($serviceAppCount === 0)
        <div class="application-settings-section-body mt-1 w-full scroll-mt-28">
            <x-empty size="sm" :title="__('common.no_application_services')"
                :description="__('common.only_database_services_description')"
                icon-name="globe" />
        </div>
    @elseif (! $hasRows)
        <div class="application-settings-section-body mt-1 w-full scroll-mt-28">
            <x-empty size="sm" :title="__('common.no_domains_configured')"
                :description="__('common.add_domain_service_description')"
                icon-name="globe" />
        </div>
    @else
        <div wire:key="service-domains-list" class="flex flex-col gap-3">
            @foreach ($domainGroups as $appId => $rows)
                @php
                    $app = collect($serviceApps)->firstWhere('id', (int) $appId);
                    $heading = \Illuminate\Support\Str::headline($app['name'] ?? $rows->first()['service_name'] ?? 'Service');
                    $hasHttpsDomains = $rows->contains(
                        fn ($row) => ! ($row['is_suggested'] ?? false) && str_starts_with(strtolower($row['url']), 'https://')
                    );
                @endphp
                <section id="service-domain-group-{{ $appId }}" wire:key="service-domain-group-{{ $appId }}"
                    x-show="matchesDomainSearch(@js($heading.' '.$rows->pluck('url')->implode(' ')))"
                    class="application-settings-section-body is-flush overflow-visible">
                    <div class="flex w-full flex-wrap items-center gap-3 rounded-t-lg border-b border-neutral-200 bg-neutral-50 px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]">
                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-black dark:text-white">{{ $heading }}</span>
                        @if ($hasHttpsDomains)
                            <div class="flex w-full items-center gap-2 sm:w-auto service-domains-https">
                                <label for="service-force-https-{{ $appId }}-trigger" class="mb-0! whitespace-nowrap text-[12px]!">{{ __('common.redirect_http_https') }}</label>
                                <x-helper helper="Disable only when Cloudflare Tunnel or another proxy connects to Coolify over HTTP. Keep enabled when Cloudflare uses Full or Full (Strict) SSL." />
                                <x-forms.listbox canGate="update" :canResource="$service" id="forceHttpsRedirects.{{ $appId }}"
                                    htmlId="service-force-https-{{ $appId }}" preserveValue
                                    onChange="updateForceHttps"
                                    :onChangeArgs="[(int) $appId]"
                                    :options="[
                                        ['value' => true, 'label' => __('common.enabled')],
                                        ['value' => false, 'label' => __('common.disabled')],
                                    ]" :disabled="! auth()->user()->can('update', $service)" />
                            </div>
                        @endif
                    </div>

                    <div wire:key="service-domain-rows-{{ $appId }}">
                        @include('livewire.project.service.partials.domain-table', [
                            'rows' => $rows,
                            'domainRows' => $domainRows,
                            'service' => $service,
                            'showServiceColumn' => false,
                            'showHeader' => true,
                        ])
                    </div>
                </section>
            @endforeach
            <div x-cloak
                x-show="domainSearch.trim() && !hasDomainSearchResults(@js($domainSearchValues))"
                class="px-4 py-8">
                <x-empty size="sm" :title="__('common.no_domains_found')"
                    :description="__('common.no_domain_search_match')" icon-name="search" />
            </div>
        </div>
    @endif

    {{-- One dialog for the address and domain settings. --}}
    <div class="relative h-auto w-auto" :class="{ 'z-40': modalOpen }"
        @keydown.window.escape="if (modalOpen) { closeEditDomain() }">
        <template x-teleport="body">
            <div x-show="modalOpen" class="fixed inset-0 z-99 overflow-y-auto" x-cloak>
                <div x-show="modalOpen" x-transition:enter="ease-out duration-100"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-100" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 h-full w-full bg-black/50 backdrop-blur-[2px]"
                    @click="closeEditDomain()"></div>
                <div class="relative flex min-h-full items-start justify-center p-4 sm:items-center">
                    <div x-show="modalOpen" x-trap.inert.noscroll="modalOpen"
                        x-transition:enter="ease-out duration-100"
                        x-transition:enter-start="opacity-0 -translate-y-2 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 -translate-y-2 sm:scale-95"
                        class="application-settings-form application-settings-section relative flex max-h-[calc(100dvh-2rem)] w-full flex-col overflow-hidden lg:w-auto lg:min-w-2xl lg:max-w-4xl"
                        style="box-shadow: 0 0 0 1px var(--coollabs-hairline), var(--shadow-modal)">
                        <header class="flex-nowrap!">
                            <h3 class="min-w-0 flex-1 truncate">{{ __('common.domain_settings') }}</h3>
                            <button type="button" @click="closeEditDomain()" class="icon-button shrink-0"
                                aria-label="Close">
                                <x-reicon name="x" class="size-4" />
                            </button>
                        </header>
                        <div class="application-settings-section-body relative min-h-0 flex-1 overflow-y-auto"
                            style="-webkit-overflow-scrolling: touch;">
                            <form wire:submit="updateDomain" class="flex flex-col gap-4">
                                <div x-show="editingServiceLabel" x-cloak class="w-full">
                                    <div class="mb-1.5 flex h-4 w-full items-center gap-1.5">
                                    <label class="mb-0! flex items-center gap-1 text-sm font-medium leading-4">{{ __('common.service_application') }}</label>
                                    </div>
                                    <input type="text" class="input" readonly x-bind:value="editingServiceLabel" />
                                    <p class="mt-1 text-[12px] text-neutral-500 dark:text-fg-dim">
                                        {{ __('common.domains_stay_on_service') }}
                                    </p>
                                </div>

                                <x-forms.domain-input id="editingDomainParts" errorId="editingDomain" />

                                @if ($editDomainDnsFailed)
                                    <x-callout type="danger" :title="__('common.dns_not_pointing_to_ip')">
                                        {{ __('common.dns_domain_not_resolved') }}
                                        {{ __('common.dns_traffic_warning') }}
                                        {{ __('common.dns_save_anyway_question') }}
                                        @if (filled($editDomainDnsMessage))
                                            <div class="pt-2">{{ $editDomainDnsMessage }}</div>
                                        @endif
                                    </x-callout>
                                @endif

                                @if ($editDomainDnsFailed)
                                    <div class="flex justify-end">
                                        <x-forms.button type="button" isError wire:click="confirmUpdateDomainDespiteDns">
                                            Continue
                                        </x-forms.button>
                                    </div>
                                @endif
                            </form>
                            @can('update', $service)
                                    <div
                                        class="mt-4 grid grid-cols-1 gap-4 border-t border-neutral-200 pt-4 sm:grid-cols-2 dark:border-white/10">
                                        <x-forms.listbox id="editingIndexing" htmlId="service-domain-indexing"
                                            :label="__('common.search_engine_indexing')" portal
                                            :options="[
                                                ['value' => 'index', 'label' => __('common.indexable')],
                                                ['value' => 'noindex', 'label' => __('common.noindex')],
                                            ]" />
                                        <x-forms.listbox id="editingRedirect" htmlId="service-domain-direction"
                                            :label="__('common.www_redirect')"
                                            :helper="__('common.service_application_domain_redirect_helper')"
                                            portal
                                            :options="[
                                                ['value' => 'both', 'label' => __('common.no_redirect')],
                                                ['value' => 'www', 'label' => __('common.redirect_to_www')],
                                                ['value' => 'non-www', 'label' => __('common.redirect_to_non_www')],
                                            ]" />
                                    </div>
                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-neutral-200 pt-4 dark:border-white/10">
                                        <x-forms.button type="button" wire:click="regenerateEditingDomain">{{ __('common.regenerate_hostname') }}</x-forms.button>
                                        @unless ($editDomainDnsFailed)
                                            <x-forms.button type="button" wire:click="updateDomain" isHighlighted>{{ __('common.save') }}</x-forms.button>
                                        @endunless
                                    </div>
                            @endcan

                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <x-domain-conflict-modal :conflicts="$domainConflicts" :showModal="$showDomainConflictModal"
        confirmAction="confirmDomainUsage" />

    @if ($showPortWarningModal)
        <div x-data="{ modalOpen: true }"
            @keydown.escape.window="modalOpen = false; $wire.call('cancelRemovePort')"
            :class="{ 'z-40': modalOpen }" class="relative">
            <template x-teleport="body">
                <div x-show="modalOpen"
                    class="fixed inset-0 z-99 flex min-h-full items-center justify-center overflow-y-auto p-4" x-cloak>
                    <div class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"></div>
                    <div x-show="modalOpen" x-trap.inert.noscroll="modalOpen"
                        class="application-settings-form application-settings-section relative w-full lg:min-w-[36rem] lg:max-w-2xl"
                        style="box-shadow: 0 0 0 1px var(--coollabs-hairline), var(--shadow-modal)">
                        <header>
                            <h3>{{ __('common.use_different_port') }}</h3>
                            <button type="button"
                                @click="modalOpen = false; $wire.call('cancelRemovePort')"
                                class="icon-button" aria-label="Close">
                                <x-reicon name="x" class="size-4" />
                            </button>
                        </header>
                        <div class="application-settings-section-body">
                            <x-callout type="warning" :title="__('common.port_requirement')" class="mb-4">
                                {!! __('common.service_port_warning', ['port' => '<strong>'.$requiredPort.'</strong>']) !!}
                            </x-callout>

                            <div class="mt-4 flex flex-wrap justify-end gap-2 border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                                <x-forms.button type="button"
                                    wire:click="cancelRemovePort"
                                    @click="modalOpen = false">
                                    {{ __('common.keep_required_port') }}
                                </x-forms.button>
                                <x-forms.button type="button" wire:click="confirmRemovePort"
                                    @click="modalOpen = false" isError>
                                    {{ __('common.use_port_anyway') }}
                                </x-forms.button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    @endif
</div>
