<div>
    <x-slot:title>
        {{ $github_app->name ?: __('common.github_app') }} | {{ __('common.sources') }} | Coolify
    </x-slot>

    @if (data_get($github_app, 'app_id'))
        @php
            $githubAppRouteParameters = ['github_app_uuid' => $github_app->uuid];
            $showSettingsSidebar = in_array($activeTab, ['general', 'permissions', 'resources', 'danger'], true);
            $settingsMenuItems = [
                [
                    'label' => 'General',
                    'route' => 'source.github.show',
                    'active' => $activeTab === 'general',
                    'icon' => 'settings',
                ],
                [
                    'label' => 'Permissions',
                    'route' => 'source.github.permissions',
                    'active' => $activeTab === 'permissions',
                    'icon' => 'keys',
                ],
                [
                    'label' => 'Resources',
                    'route' => 'source.github.resources',
                    'active' => $activeTab === 'resources',
                    'icon' => 'grid',
                ],
                [
                    'label' => 'Danger Zone',
                    'route' => 'source.github.danger',
                    'active' => $activeTab === 'danger',
                    'icon' => 'shield-alert',
                ],
            ];
            $settingsMenuTranslations = [
                'General' => 'common.general',
                'Permissions' => 'common.permissions',
                'Resources' => 'common.resources',
                'Danger Zone' => 'common.danger_zone',
            ];
        @endphp

        <x-dashboard.navbar section="source" :parameters="$githubAppRouteParameters"
            :title="$name ?: __('common.github_app')"
            :subtitle="filled($organization) ? __('source.github_app_for', ['organization' => $organization]) : __('source.private_github_source')"
            :mobileTitleOnly="true" />

        @if ($showSettingsSidebar)
            <section class="application-settings-workspace mt-4 w-full max-w-none lg:mt-0">
                <div class="grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
                    <aside class="application-settings-navigation min-w-0 xl:self-start">
                        <nav :aria-label="__('source.github_app_settings')"
                            class="grid grid-cols-2 gap-0.5 border-y border-neutral-200 py-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-1 xl:border-y-0 xl:py-0 dark:border-white/[0.06]">
                            <div class="nav-section hidden xl:block">{{ __('common.settings') }}</div>
                            @foreach ($settingsMenuItems as $menuItem)
                                <a wire:key="github-app-settings-{{ str($menuItem['label'])->slug() }}"
                                    @class([
                                        'menu-item',
                                        'menu-item-active' => $menuItem['active'],
                                    ])
                                    {{ wireNavigate() }}
                                    href="{{ route($menuItem['route'], $githubAppRouteParameters) }}">
                                    <x-reicon :name="$menuItem['icon']" class="menu-item-icon" />
                                    <span class="menu-item-label">{{ __($settingsMenuTranslations[$menuItem['label']] ?? $menuItem['label']) }}</span>
                                </a>
                            @endforeach
                        </nav>
                    </aside>

                    <div class="min-w-0">
                        @if (!data_get($github_app, 'installation_id') && $activeTab === 'general')
                            <div class="application-settings-form">
                                <x-application.settings-section :title="__('source.complete_github_installation')"
                                    :description="__('source.complete_github_installation_description')">
                                    <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                                <x-reicon name="alert-triangle" class="size-4" />
                                            </div>
                                            <p class="max-w-xl text-[12px] leading-5 text-neutral-500 dark:text-fg-dim">
                                                {{ __('source.repository_access_not_installed') }}
                                            </p>
                                        </div>
                                        <a class="button shrink-0 button-highlighted"
                                            href="{{ getInstallationPath($github_app) }}">
                                            {{ __('source.install_repositories') }}
                                            <x-external-link />
                                        </a>
                                    </div>
                                </x-application.settings-section>
                            </div>
                        @elseif ($activeTab === 'general')
                            @php
                                $privateKeyOptions = collect([
                                    blank($github_app->private_key_id)
                                        ? ['value' => 0, 'label' => __('source.select_private_key')]
                                        : null,
                                    ...$privateKeys->map(fn ($privateKey) => [
                                        'value' => $privateKey->id,
                                        'label' => $privateKey->name,
                                    ])->all(),
                                ])->filter()->values()->all();
                            @endphp

                            <form wire:submit="submit" class="application-settings-form">
                                <x-unsaved-bar action="submit" />
                                <x-application.settings-section :title="__('common.general')"
                                    :description="__('source.github_connection_description')">
                                    <x-slot:actions>
                                        <x-forms.button type="button" wire:click.prevent="updateGithubAppName">
                                            <x-reicon name="refresh" class="size-3.5" />
                                            {{ __('source.sync_name') }}
                                        </x-forms.button>
                                        @can('update', $github_app)
                                            <a href="{{ $this->getGithubAppNameUpdatePath() }}" class="button">
                                                {{ __('source.rename') }}
                                                <x-external-link />
                                            </a>
                                            <a href="{{ getInstallationPath($github_app) }}" class="button">
                                                {{ __('source.repositories') }}
                                                <x-external-link />
                                            </a>
                                        @endcan
                                    </x-slot:actions>

                                    <div class="grid gap-4 lg:grid-cols-2">
                                        <x-forms.input canGate="update" :canResource="$github_app" id="name" :label="__('source.app_name')" />
                                        <x-forms.input canGate="update" :canResource="$github_app" id="organization"
                                            :label="__('source.organization')" :placeholder="__('source.personal_account_when_empty')" />

                                        @if (!isCloud())
                                            <div class="lg:col-span-2">
                                                <x-forms.listbox canGate="update" :canResource="$github_app" id="isSystemWide" :label="__('source.availability')" :options="[
                                                    ['value' => false, 'label' => __('source.only_this_team')],
                                                    ['value' => true, 'label' => __('source.every_team_instance')],
                                                ]"
                                                    :helper="__('source.system_wide_github_helper')"
                                                    :disabled="!auth()->user()->can('update', $github_app)" />
                                            </div>
                                            @if ($isSystemWide)
                                                <div class="lg:col-span-2">
                                                    <x-callout type="warning" :title="__('source.shared_with_every_team')">
                                                        {{ __('source.team_specific_isolation') }}
                                                    </x-callout>
                                                </div>
                                            @endif
                                        @endif

                                        <x-forms.input canGate="update" :canResource="$github_app" id="htmlUrl"
                                            :label="__('source.html_url')" />
                                        <x-forms.input canGate="update" :canResource="$github_app" id="apiUrl"
                                            :label="__('source.api_url')" />
                                        <x-forms.input canGate="update" :canResource="$github_app" id="customUser"
                                            :label="__('source.user')" required />
                                        <x-forms.input canGate="update" :canResource="$github_app" type="number"
                                            id="customPort" :label="__('source.port')" required />
                                        <x-forms.input canGate="update" :canResource="$github_app" type="number" id="appId"
                                            :label="__('source.app_id')" required />
                                        <x-forms.input canGate="update" :canResource="$github_app" type="number"
                                            id="installationId" :label="__('source.installation_id')" required />
                                        <x-forms.input canGate="update" :canResource="$github_app" id="clientId"
                                            :label="__('source.client_id')" type="password" required />
                                        <x-forms.input canGate="update" :canResource="$github_app" id="clientSecret"
                                            :label="__('source.client_secret')" type="password" required />
                                        <x-forms.input canGate="update" :canResource="$github_app" id="webhookSecret"
                                            :label="__('source.webhook_secret')" type="password" required />
                                        <x-forms.listbox canGate="update" :canResource="$github_app" id="privateKeyId" :label="__('source.private_key')" required
                                            :options="$privateKeyOptions" :disabled="!auth()->user()->can('update', $github_app)" />
                                    </div>
                                </x-application.settings-section>
                            </form>
                        @elseif ($activeTab === 'danger')
                            <div class="application-settings-form">
                                <x-application.settings-section id="github-app-danger-section" :title="__('common.danger_zone')"
                                    :helper="__('source.github_danger_helper')">
                                    <x-danger-zone :title="__('source.delete_github_app')">
                                                <p>
                                                    {{ __('source.delete_github_app_description', ['name' => $name ?: __('common.github_app')]) }}
                                                </p>
                                                <ul class="space-y-1 text-xs">
                                                    <li>{{ __('source.github_registration_not_removed') }}</li>
                                                    <li>{{ __('source.linked_applications_keep_settings') }}</li>
                                                    <li>{{ __('source.github_source_not_restored') }}</li>
                                                </ul>
                                            <x-slot:action>
                                                @can('delete', $github_app)
                                                    <x-modal-confirmation :title="__('source.confirm_github_app_deletion')" isErrorButton
                                                        :buttonTitle="__('common.delete')" submitAction="delete"
                                                        :actions="[__('source.selected_github_app_deleted')]"
                                                        confirmationText="{{ data_get($github_app, 'name') }}"
                                                        :confirmationLabel="__('source.confirm_github_app_name')"
                                                        :shortConfirmationLabel="__('source.github_app_name')" :confirmWithPassword="false"
                                                        :step2ButtonText="__('common.permanently_delete_button')" />
                                                @else
                                                    <x-forms.button isError disabled :tooltip="__('source.no_permission_delete_github_app')">
                                                        {{ __('common.delete') }}
                                                    </x-forms.button>
                                                @endcan
                                            </x-slot:action>
                                    </x-danger-zone>

                                    @cannot('delete', $github_app)
                                        <div class="mt-4">
                                            <x-callout type="danger" :title="__('common.insufficient_permissions')">
                                                {{ __('source.github_app_delete_admin_help') }}
                                            </x-callout>
                                        </div>
                                    @endcannot
                                </x-application.settings-section>
                            </div>
                        @elseif ($activeTab === 'permissions')
                            @include('livewire.source.github.permissions')
                        @elseif ($activeTab === 'resources')
                            @include('livewire.source.github.resources')
                        @endif
                    </div>
                </div>
            </section>
        @endif
    @else
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="truncate text-[24px]! leading-7! font-semibold! tracking-tight!">
                    {{ $name ?: __('common.github_app') }}
                </h1>
                <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
                    {{ __('source.finish_registering_github_app') }}
                </p>
            </div>
            @can('delete', $github_app)
                <div class="shrink-0">
                    <x-modal-confirmation :title="__('source.confirm_github_app_deletion')" isErrorButton
                        :buttonTitle="__('common.delete')" submitAction="delete"
                        :actions="[__('source.selected_github_app_deleted')]"
                        confirmationText="{{ data_get($github_app, 'name') }}"
                        :confirmationLabel="__('source.confirm_github_app_name')"
                        :shortConfirmationLabel="__('source.github_app_name')" :confirmWithPassword="false"
                        :step2ButtonText="__('common.permanently_delete_button')" />
                </div>
            @endcan
        </header>

        @can('create', $github_app)
            @php
                $endpointOptions = collect([
                    $fqdn ? ['value' => $fqdn, 'label' => __('source.use_endpoint', ['endpoint' => $fqdn])] : null,
                    $ipv4 ? ['value' => $ipv4, 'label' => __('source.use_endpoint', ['endpoint' => $ipv4])] : null,
                    $ipv6 ? ['value' => $ipv6, 'label' => __('source.use_endpoint', ['endpoint' => $ipv6])] : null,
                    config('app.url')
                        ? ['value' => config('app.url'), 'label' => __('source.use_endpoint', ['endpoint' => config('app.url')])]
                        : null,
                ])->filter()->values()->all();
            @endphp

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="application-settings-form"
                    x-data="{
                        webhookEndpoint: $wire.entangle('webhook_endpoint').live,
                        useCustomWebhookEndpoint: $wire.entangle('use_custom_webhook_endpoint').live,
                        customWebhookEndpoint: $wire.entangle('custom_webhook_endpoint').live,
                    }">
                    <x-application.settings-section :title="__('source.automated_installation')"
                        :description="__('source.automated_installation_description')">
                        <x-slot:actions>
                            <x-status-badge :label="__('source.recommended')" type="success" />
                        </x-slot:actions>

                        <div class="flex min-h-[24rem] flex-col gap-4">
                            @if (!isCloud() || isDev())
                                <x-forms.listbox id="use_custom_webhook_endpoint" :label="__('source.webhook_endpoint')"
                                    :live="true" :options="[
                                        ['value' => false, 'label' => __('source.use_instance_endpoint')],
                                        ['value' => true, 'label' => __('source.use_custom_endpoint')],
                                    ]"
                                    x-model="useCustomWebhookEndpoint"
                                    :helper="__('source.custom_endpoint_helper')" />
                                <div x-show="!useCustomWebhookEndpoint">
                                    <x-forms.listbox id="webhook_endpoint" :label="__('source.instance_endpoint')"
                                        :options="$endpointOptions" x-model="webhookEndpoint" />
                                </div>
                                <div x-cloak x-show="useCustomWebhookEndpoint">
                                    <x-forms.input canGate="create" :canResource="$github_app"
                                        x-model="customWebhookEndpoint" id="custom_webhook_endpoint" type="url"
                                        :label="__('source.custom_endpoint')" placeholder="https://coolify.example.com"
                                        :helper="__('source.do_not_include_webhooks')" />
                                </div>
                            @else
                                <p class="text-[12px] leading-5 text-neutral-500 dark:text-fg-dim">
                                    {{ __('source.register_before_using_github') }}
                                </p>
                            @endif

                            <div
                                class="rounded-lg border border-neutral-200 bg-neutral-50 p-3 text-[12px] leading-5 text-neutral-600 dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-fg-dim">
                                <p class="font-medium text-black dark:text-fg">{{ __('source.mandatory_permissions') }}</p>
                                <p class="mt-1">{{ __('source.github_permission_summary') }}</p>
                            </div>

                            <x-forms.listbox id="preview_deployment_permissions"
                                :label="__('source.preview_deployment_access')" :options="[
                                    ['value' => false, 'label' => __('source.do_not_update_pull_requests')],
                                    ['value' => true, 'label' => __('source.read_update_pull_requests')],
                                ]"
                                :helper="__('source.preview_deployment_access_helper')" />

                            <button type="button"
                                class="button mt-auto w-full justify-center button-highlighted"
                                x-on:click.prevent="createGithubApp(webhookEndpoint, useCustomWebhookEndpoint, customWebhookEndpoint, {{ Illuminate\Support\Js::from($preview_deployment_permissions) }}, {{ Illuminate\Support\Js::from($administration) }})">
                                {{ __('source.register_with_github') }}
                            </button>
                        </div>
                    </x-application.settings-section>
                </div>

                <div class="application-settings-form">
                    <x-application.settings-section :title="__('source.manual_installation')"
                        :description="__('source.manual_installation_description')">
                        <x-slot:actions>
                            <x-status-badge :label="__('source.advanced')" type="neutral" />
                        </x-slot:actions>

                        <div class="flex min-h-[24rem] flex-col">
                            <div
                                class="flex size-10 items-center justify-center rounded-xl border border-neutral-200 bg-neutral-50 text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.035] dark:text-fg-dim">
                                <x-reicon name="settings" class="size-5" />
                            </div>
                            <p class="mt-4 max-w-md text-[12px] leading-5 text-neutral-500 dark:text-fg-dim">
                                {{ __('source.manual_installation_helper') }}
                            </p>
                            <button type="button" class="button mt-auto w-fit"
                                wire:click.prevent="createGithubAppManually">
                                {{ __('source.continue_manually') }}
                                <x-reicon name="arrow-right" class="size-3.5" />
                            </button>
                        </div>
                    </x-application.settings-section>
                </div>
            </div>
        @else
            <x-callout type="danger" :title="__('common.insufficient_permissions')">
                {{ __('source.create_github_apps_permission') }}
            </x-callout>
        @endcan

        <script>
            function createGithubApp(webhook_endpoint, use_custom_webhook_endpoint, custom_webhook_endpoint,
                preview_deployment_permissions, administration) {
                const {
                    organization,
                    html_url
                } = @js($github_app->only(['organization', 'html_url']));
                const selectedEndpoint = webhook_endpoint ? webhook_endpoint.trim() : '';
                const customEndpoint = custom_webhook_endpoint ? custom_webhook_endpoint.trim() : '';
                if (use_custom_webhook_endpoint && !customEndpoint) {
                    alert(@js(__('source.custom_webhook_required')));
                    return;
                }
                if (!use_custom_webhook_endpoint && !selectedEndpoint) {
                    alert(@js(__('source.webhook_endpoint_required')));
                    return;
                }
                let baseUrl = (use_custom_webhook_endpoint ? customEndpoint : selectedEndpoint).replace(/\/+$/, '');
                const name = @js($name);
                const manifestState = @js($manifestState);
                const isDev = @js(config('app.env')) === 'local';
                const devWebhook = @js(config('constants.webhooks.dev_webhook'));
                if (isDev && devWebhook) {
                    baseUrl = devWebhook;
                }
                const webhookBaseUrl = `${baseUrl}/webhooks`;
                const organizationPath = organization ? encodeURIComponent(organization.replace(/^\/+|\/+$/g, '')) : '';
                const path = organizationPath ? `organizations/${organizationPath}/settings/apps/new` : 'settings/apps/new';
                const default_permissions = {
                    contents: 'read',
                    metadata: 'read',
                    emails: 'read',
                    administration: 'read'
                };
                const default_events = ['push'];
                if (preview_deployment_permissions) {
                    default_permissions.pull_requests = 'write';
                    default_events.push('pull_request');
                }
                if (administration) {
                    default_permissions.administration = 'write';
                }

                const data = {
                    name,
                    url: baseUrl,
                    hook_attributes: {
                        url: `${webhookBaseUrl}/source/github/events`,
                        active: true,
                    },
                    redirect_url: `${webhookBaseUrl}/source/github/redirect`,
                    callback_urls: [`${baseUrl}/login/github/app`],
                    public: false,
                    request_oauth_on_install: false,
                    setup_url: `${webhookBaseUrl}/source/github/install`,
                    setup_on_update: true,
                    default_permissions,
                    default_events
                };
                const form = document.createElement('form');
                form.setAttribute('method', 'post');
                form.setAttribute('action', `${html_url}/${path}?state=${manifestState}`);
                const input = document.createElement('input');
                input.setAttribute('id', 'manifest');
                input.setAttribute('name', 'manifest');
                input.setAttribute('type', 'hidden');
                input.setAttribute('value', JSON.stringify(data));
                form.appendChild(input);
                document.getElementsByTagName('body')[0].appendChild(form);
                form.submit();
            }
        </script>
    @endif
</div>
