<nav class="w-full max-w-none pb-3 lg:pb-0" wire:poll.30s="refreshAgentStatus">
    <x-process-dialog @startproxy.window="processDialogOpen = true" closeWithX>
        <x-slot:title>{{ __('common.proxy_startup_logs') }}</x-slot:title>
        <x-slot:content>
            <div class="flex h-full min-h-0 flex-col gap-3">
                @if ($server->id === 0)
                    <div class="shrink-0 rounded-lg border border-warning/30 bg-warning/10 p-3 text-sm text-warning">
                        <span class="font-semibold">{{ __('common.note') }}:</span>
                        {{ __('common.localhost_server_notice') }}
                        {{ __('common.proxy_restart_connection_notice') }}
                        {{ __('common.refresh_browser_after_proxy_notice') }}
                    </div>
                @endif
                <div class="flex min-h-0 flex-1 flex-col">
                    <livewire:activity-monitor :header="__('common.logs')" fullHeight />
                </div>
            </div>
        </x-slot:content>
    </x-process-dialog>

    @php
        $serverRouteParameters = ['server_uuid' => $server->uuid];
        $serverMenuItems = [
            [
                'label' => __('common.configuration'),
                'route' => 'server.show',
                'active' => request()->routeIs(
                    'server.show',
                    'server.advanced',
                    'server.private-key',
                    'server.cloud-provider-token',
                    'server.ca-certificate',
                    'server.cloudflare-tunnel',
                    'server.docker-cleanup',
                    'server.destinations',
                    'server.log-drains',
                    'server.metrics',
                    'server.swarm',
                    'server.delete',
                ),
            ],
            [
                'label' => __('common.proxy'),
                'route' => 'server.proxy',
                'active' => request()->routeIs('server.proxy', 'server.proxy.*'),
                'visible' => ! $server->isSwarmWorker() && ! $server->settings->is_build_server,
                'warning' => $this->hasTraefikOutdated || $this->hasPendingProxyConfiguration,
            ],
            [
                'label' => __('common.sentinel'),
                'route' => 'server.sentinel',
                'active' => request()->routeIs('server.sentinel', 'server.sentinel.*'),
                'visible' => $server->isFunctional()
                    && ! $server->isSwarm()
                    && ! $server->settings->is_build_server
                    && auth()->user()?->can('viewSentinel', $server),
                'warning' => $sentinelWarningOverride ?? ($server->isSentinelEnabled() && $server->sentinelStatus() === 'out_of_sync'),
            ],
            [
                'label' => __('common.resources'),
                'route' => 'server.resources',
                'active' => request()->routeIs('server.resources'),
            ],
            [
                'label' => __('common.terminal'),
                'route' => 'server.command',
                'active' => $currentRoute === 'server.command',
                'navigate' => false,
                'visible' => auth()->user()?->can('canAccessTerminal'),
            ],
            [
                'label' => __('common.security'),
                'route' => 'server.security.patches',
                'active' => request()->routeIs('server.security.*'),
                'visible' => auth()->user()?->can('update', $server),
            ],
        ];

        $serverMenuItems = array_values(array_filter(
            $serverMenuItems,
            fn (array $item): bool => $item['visible'] ?? true,
        ));
        $activeServerMenuItem = collect($serverMenuItems)->firstWhere('active', true) ?? $serverMenuItems[0];
        $activeServerMenuNavigation = ($activeServerMenuItem['navigate'] ?? true) ? 'navigate' : 'location';
        $activeServerMenuValue = $activeServerMenuNavigation.'|'.route(
            $activeServerMenuItem['route'],
            $serverRouteParameters,
        );
        $showSentinelStatus = $server->isFunctional() && $server->isSentinelEnabled();
        $proxyCanBeStopped = in_array($proxyStatus, ['running', 'starting', 'restarting'], true);
    @endphp

    @if ($this->hasPendingProxyConfiguration)
        @teleport('#configuration-warning-hud-slot')
            <div>
                <x-proxy-configuration-warning :can-restart="auth()->user()?->can('manageProxy', $server) ?? false" />
            </div>
        @endteleport

        @teleport('#configuration-warning-hud-slot-mobile')
            <div>
                <x-proxy-configuration-warning :can-restart="auth()->user()?->can('manageProxy', $server) ?? false" />
            </div>
        @endteleport
    @endif

    @teleport('#server-topbar-context')
        <div data-testid="server-topbar-context"
            class="flex min-w-0 items-center gap-1 text-[13px]">
            <span class="shrink-0 px-0.5 text-neutral-300 dark:text-fg-faint">/</span>
            <span class="relative flex min-w-0 shrink items-center gap-2 px-1"
                x-data="{ open: false, search: '', servers: @js($serverSwitcherOptions) }"
                @click.outside="open = false" @keydown.escape.window="open = false">
                <button type="button"
                    class="flex h-8 max-w-56 min-w-0 items-center gap-1.5 rounded-md px-2 transition-colors hover:bg-neutral-100 dark:hover:bg-white/[0.05] xl:max-w-72"
                    @click="open = !open" :aria-expanded="open" aria-label="{{ __('common.switch_server') }}">
                    <span class="min-w-0 truncate font-semibold text-black dark:text-fg">
                        {{ $server->name }}
                    </span>
                    <svg class="size-4 shrink-0 text-neutral-400 dark:text-fg-faint" viewBox="0 0 24 24"
                        fill="none" aria-hidden="true">
                        <path d="M8 9l4-4 4 4M8 15l4 4 4-4" stroke="currentColor" stroke-width="1.6"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
                <div x-cloak x-show="open" x-transition.origin.top.left
                    class="listbox-panel top-9! left-1! z-[90]! w-64! min-w-0!">
                    <div class="border-b border-neutral-200 p-1.5 dark:border-white/[0.08]">
                        <div class="relative">
                            <x-reicon name="search"
                                class="pointer-events-none absolute top-1/2 left-2 size-3 -translate-y-1/2 text-neutral-400 dark:text-fg-faint" />
                            <input type="search" x-model.debounce.100ms="search" placeholder="{{ __('common.filter_servers') }}"
                                class="input h-7! w-full rounded-md! border-neutral-200! bg-white! py-0! pr-2! pl-7! text-[11px]! text-black! placeholder:text-neutral-400! dark:border-white/[0.1]! dark:bg-coolgray-100! dark:text-white! dark:placeholder:text-fg-faint!">
                        </div>
                    </div>
                    <template x-for="option in servers.filter((item) => item.name.toLowerCase().includes(search.toLowerCase()))"
                        :key="option.uuid">
                        <a :href="option.href" {{ wireNavigate() }} @click="open = false"
                            class="listbox-option gap-2.5!">
                            <span class="size-1.5 shrink-0 rounded-full"
                                :class="option.functional ? 'bg-success' : 'bg-error'"></span>
                            <span class="min-w-0 flex-1 truncate" x-text="option.name"></span>
                            <x-reicon name="check-circle"
                                class="size-3.5 shrink-0 text-coollabs dark:text-warning"
                                x-show="option.uuid === '{{ $server->uuid }}'" />
                        </a>
                    </template>
                </div>
            </span>
            <x-server.status-summary :server="$server" :proxy-status="$proxyStatus"
                :show-sentinel-status="$showSentinelStatus" />
        </div>
    @endteleport

    <div>
        {{-- Name lives in the desktop topbar (teleport) from lg up. Below that the
             mobile topbar has no resource context, so show the H1 through lg. --}}
        <div class="mb-3 w-full lg:hidden">
            <div class="flex min-w-0 flex-col gap-2">
                <h1 data-testid="server-subtitle"
                    class="min-w-0 truncate text-[24px]! leading-7! font-semibold! tracking-tight! text-black dark:text-fg">
                    {{ $server->name }}
                </h1>
                <x-server.status-summary :server="$server" :proxy-status="$proxyStatus"
                    :show-sentinel-status="$showSentinelStatus" />
            </div>
        </div>

        {{-- Resource actions stay available below the desktop action HUD. Navigation lives in the sidebar. --}}
        <div class="w-full xl:hidden">
            @if ($server->proxySet())
                @can('manageProxy', $server)
                    <div id="server-mobile-actions" class="relative mb-3"
                        x-data="{ open: false }" @click.outside="open = false"
                        @keydown.escape.window="open = false">
                        <button type="button" class="button w-full justify-between" @click="open = !open"
                            :aria-expanded="open" aria-haspopup="menu">
                            <span class="inline-flex items-center gap-2">
                                {{ __('common.actions') }}
                            </span>
                            <span class="inline-flex transition-transform" :class="open && 'rotate-180'">
                                <x-reicon name="chevron-down" class="size-3 opacity-55" />
                            </span>
                        </button>

                        <div x-cloak x-show="open" x-transition.origin.top.left
                            class="listbox-panel top-full! left-0! right-0! mt-1! w-full! min-w-0!" role="menu">
                            @if ($proxyCanBeStopped)
                                <button type="button" class="listbox-option justify-start! gap-2.5!"
                                    @click="open = false; document.getElementById('server-mobile-restart-proxy-trigger')?.click()"
                                    role="menuitem">
                                    <span class="flex size-4 shrink-0 items-center justify-center">
                                            <x-reicon name="restart" class="size-3.5 text-orange-500 dark:text-warning" />
                                    </span>
                                    {{ __('common.restart_proxy') }}
                                </button>
                                <button type="button" class="listbox-option justify-start! gap-2.5!"
                                    @click="open = false; document.getElementById('server-mobile-stop-proxy-trigger')?.click()"
                                    role="menuitem">
                                    <span class="flex size-4 shrink-0 items-center justify-center">
                                        <x-reicon name="stop-circle" class="size-3.5 text-error" />
                                    </span>
                                    {{ __('common.stop') }} {{ __('common.proxy') }}
                                </button>
                                @if ($traefikDashboardAvailable)
                                    <a class="listbox-option justify-start! gap-2.5!" target="_blank"
                                        href="http://{{ $serverIp }}:8080" @click="open = false" role="menuitem">
                                        <span class="flex size-4 shrink-0 items-center justify-center">
                                        <x-reicon name="external-link" class="size-3! opacity-70" />
                                        </span>
                                        {{ __('common.traefik_dashboard') }}
                                    </a>
                                @endif
                            @else
                                <button type="button" class="listbox-option justify-start! gap-2.5!"
                                    @click="open = false; $wire.dispatch('checkProxyEvent')" role="menuitem">
                                    <span class="flex size-4 shrink-0 items-center justify-center">
                                            <x-reicon name="play-circle" class="size-3.5 text-warning" />
                                    </span>
                                    {{ __('common.start') }} {{ __('common.proxy') }}
                                </button>
                            @endif
                            <button type="button" class="listbox-option justify-start! gap-2.5!"
                                wire:click="checkProxyStatus" wire:loading.attr="disabled"
                                @click="open = false" role="menuitem">
                                <span class="flex size-4 shrink-0 items-center justify-center">
                                    <x-reicon name="refresh" class="size-3.5 opacity-70" />
                                </span>
                                {{ __('common.refresh_proxy_status') }}
                            </button>
                        </div>
                    </div>

                    {{-- Programmatic open only (clicked from the Actions menu). Keep fully
                         display:none so the modal shells never reserve a layout row. --}}
                    <div class="hidden" aria-hidden="true">
                        <x-modal-confirmation :title="__('common.confirm_proxy_restart')" :buttonTitle="__('common.restart_proxy')"
                            submitAction="restart" :actions="[
                                __('common.proxy_restart_action'),
                                __('common.proxy_restart_unavailable'),
                            ]" :confirmWithText="false" :confirmWithPassword="false" :step2ButtonText="__('common.restart_proxy')"
                            :dispatchEvent="true" dispatchEventType="restartEvent">
                            <x-slot:trigger>
                                <button id="server-mobile-restart-proxy-trigger" type="button">
                                    {{ __('common.restart_proxy') }}
                                </button>
                            </x-slot:trigger>
                        </x-modal-confirmation>
                        <x-modal-confirmation :title="__('common.confirm_proxy_stop')" :buttonTitle="__('common.stop') . ' ' . __('common.proxy')"
                            submitAction="stop(true)" :actions="[
                                __('common.proxy_stop_action'),
                                __('common.proxy_stop_unavailable'),
                            ]" :confirmWithText="false" :confirmWithPassword="false" :step2ButtonText="__('common.stop') . ' ' . __('common.proxy')"
                            :dispatchEvent="true" dispatchEventType="stopEvent">
                            <x-slot:trigger>
                                <button id="server-mobile-stop-proxy-trigger" type="button">
                                    {{ __('common.stop') }} {{ __('common.proxy') }}
                                </button>
                            </x-slot:trigger>
                        </x-modal-confirmation>
                    </div>
                @endcan
            @endif

            <div class="hidden" aria-hidden="true">
                <x-resource-heading-tabs class="min-w-0 flex-1">
                    @foreach ($serverMenuItems as $menuItem)
                        <a @class([
                            'app-tab shrink-0',
                            'app-tab-active' => $menuItem['active'],
                        ])
                            @if ($menuItem['active']) aria-current="page" @endif
                            @if ($menuItem['navigate'] ?? true) {{ wireNavigate() }} @endif
                            href="{{ route($menuItem['route'], $serverRouteParameters) }}">
                            {{ $menuItem['label'] }}
                        </a>
                    @endforeach
                </x-resource-heading-tabs>
            </div>
        </div>

        @teleport('#resource-action-hud-slot')
        <div class="hidden xl:flex xl:w-auto xl:items-center">
            <div
                class="resource-heading-navbar application-heading-actions flex w-auto min-w-0 items-center justify-end gap-1 overflow-visible">
                <x-resource-heading-tabs class="hidden" aria-hidden="true">
                    @foreach ($serverMenuItems as $menuItem)
                        <a wire:key="server-primary-nav-{{ str($menuItem['label'])->slug() }}"
                            @class([
                                'app-tab shrink-0 gap-1',
                                'app-tab-active' => $menuItem['active'],
                            ])
                            @if ($menuItem['active']) aria-current="page" @endif
                            @if ($menuItem['navigate'] ?? true) {{ wireNavigate() }} @endif
                            href="{{ route($menuItem['route'], $serverRouteParameters) }}">
                            {{ $menuItem['label'] }}
                            @if ($menuItem['warning'] ?? false)
                                <x-reicon name="alert-triangle"
                                    class="size-3.5 text-orange-500 dark:text-warning" />
                            @endif
                        </a>
                    @endforeach
                </x-resource-heading-tabs>

                @if ($server->proxySet())
                    @can('manageProxy', $server)
                        <div id="server-desktop-actions" class="resource-heading-actions relative shrink-0"
                            x-data="{ open: false }" x-effect="$dispatch('resource-actions-toggled', { open })"
                            @click.outside="open = false"
                            @keydown.escape.window="open = false">
                            <button type="button" class="button button-highlighted" @click="open = !open" :aria-expanded="open"
                                aria-haspopup="menu" wire:loading.attr="disabled" wire:loading.class="is-loading"
                                wire:target="checkProxy,startProxy">
                                {{ __('common.actions') }}
                                <x-reicon name="chevron-down" class="size-3 opacity-55" />
                            </button>

                            <div x-cloak x-show="open" x-transition.origin.top.right
                                class="listbox-panel top-full! right-0! left-auto! mt-1! w-60! min-w-0!" role="menu">
                                @if ($proxyCanBeStopped)
                                    <button type="button" class="listbox-option justify-start! gap-2.5!"
                                        @click="open = false; document.getElementById('server-mobile-restart-proxy-trigger')?.click()"
                                        role="menuitem">
                                        <span class="flex size-4 shrink-0 items-center justify-center">
                                            <x-reicon name="restart" class="size-3.5 opacity-70" />
                                        </span>
                                        {{ __('common.restart_proxy') }}
                                    </button>
                                    <button type="button" class="listbox-option justify-start! gap-2.5!"
                                        @click="open = false; document.getElementById('server-mobile-stop-proxy-trigger')?.click()"
                                        role="menuitem">
                                        <span class="flex size-4 shrink-0 items-center justify-center">
                                            <x-reicon name="stop-circle" class="size-3.5 text-error" />
                                        </span>
                                        {{ __('common.stop') }} {{ __('common.proxy') }}
                                    </button>
                                @else
                                    <button type="button" class="listbox-option justify-start! gap-2.5!"
                                        @click="open = false; $wire.dispatch('checkProxyEvent')" role="menuitem">
                                        <span class="flex size-4 shrink-0 items-center justify-center">
                                            <x-reicon name="play-circle" class="size-3.5 opacity-70" />
                                        </span>
                                        {{ __('common.start') }} {{ __('common.proxy') }}
                                    </button>
                                @endif
                                <div class="my-1 border-t border-coolgray-200 dark:border-coolgray-300"
                                    role="separator"></div>
                                <button type="button" class="listbox-option justify-start! gap-2.5!"
                                    wire:click="checkProxyStatus" wire:loading.attr="disabled"
                                    @click="open = false" role="menuitem">
                                    <span class="flex size-4 shrink-0 items-center justify-center">
                                            <x-reicon name="refresh" class="size-3.5 opacity-70" />
                                    </span>
                                    {{ __('common.refresh_proxy_status') }}
                                </button>
                                @if ($traefikDashboardAvailable)
                                    <a class="listbox-option justify-start! gap-2.5!" target="_blank"
                                        href="http://{{ $serverIp }}:8080" @click="open = false" role="menuitem">
                                        <span class="flex size-4 shrink-0 items-center justify-center">
                                            <x-reicon name="external-link" class="size-3! opacity-70" />
                                        </span>
                                        {{ __('common.traefik_dashboard') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endcan
                @endif
            </div>
        </div>
        @endteleport
    </div>

    @script
        <script>
            $wire.$on('checkProxyEvent', () => {
                try {
                    $wire.$call('checkProxy');
                } catch (error) {
                    console.error(error);
                    $wire.$dispatch('error', @js(__('common.failed_check_proxy_status')));
                }
            });
            $wire.$on('restartEvent', () => {
                if ($wire.restartInitiated) return;
                window.dispatchEvent(new CustomEvent('startproxy'));
                $wire.$call('restart');
            });
            $wire.$on('startProxy', () => {
                window.dispatchEvent(new CustomEvent('startproxy'));
                $wire.$call('startProxy');
            });
            $wire.$on('stopEvent', () => {
                $wire.$call('stop');
            });
        </script>
    @endscript
</nav>
