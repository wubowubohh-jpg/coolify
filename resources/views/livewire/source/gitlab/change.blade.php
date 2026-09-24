<div>
    <x-slot:title>
        {{ $name ?: __('common.gitlab_app') }} | {{ __('common.sources') }} | Coolify
    </x-slot>

    @if ($isConnected)
        <header class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="truncate text-[24px]! leading-7! font-semibold! tracking-tight!">
                        {{ $name ?: __('common.gitlab_app') }}
                    </h1>
                    <x-status-badge :label="__('common.connected')" type="success" />
                </div>
                <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
                    {{ filled($groupName) ? __('source.gitlab_app_for', ['group' => $groupName]) : __('source.private_gitlab_source') }}
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2 sm:ml-auto">
                @can('view', $gitlab_app)
                    <x-forms.button type="button" wire:click.prevent="testConnection">
                        {{ __('source.test_connection') }}
                    </x-forms.button>
                @endcan
                @can('delete', $gitlab_app)
                    <x-modal-confirmation :title="__('source.confirm_gitlab_app_deletion')" isErrorButton :buttonTitle="__('common.delete')"
                        submitAction="delete" :actions="[__('source.selected_gitlab_app_deleted')]"
                        confirmationText="{{ data_get($gitlab_app, 'name') }}"
                        :confirmationLabel="__('source.confirm_gitlab_app_name')"
                        :shortConfirmationLabel="__('source.gitlab_app_name')" :confirmWithPassword="false"
                        :step2ButtonText="__('common.permanently_delete_button')" />
                @endcan
            </div>
        </header>

        <form wire:submit="submit" class="application-settings-form">
            <x-unsaved-bar action="submit" />

            <x-application.settings-section :title="__('common.general')"
                :description="__('source.gitlab_connection_description')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="name" :label="__('common.name')" />

                    @if (! isCloud())
                        <div class="lg:col-span-2 max-w-xs">
                            <x-forms.checkbox canGate="update" :canResource="$gitlab_app" :label="__('source.system_wide')"
                                :helper="__('source.system_wide_gitlab_helper')"
                                instantSave id="isSystemWide" />
                        </div>
                        @if ($isSystemWide)
                            <div class="lg:col-span-2">
                                <x-callout type="warning" :title="__('source.shared_with_every_team')">
                                    {{ __('source.team_specific_isolation') }}
                                </x-callout>
                            </div>
                        @endif
                    @endif
                </div>
            </x-application.settings-section>

            <x-application.settings-section :title="__('source.oauth_credentials')"
                :description="__('source.oauth_credentials_description')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="clientId"
                        :label="__('source.application_id')" />
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="clientSecretInput"
                        :label="__('source.application_secret')" type="password"
                        :helper="__('source.stored_encrypted_keep_secret')" />
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="groupName" :label="__('source.group_name')"
                        :helper="__('source.group_name_filter_helper')" />
                </div>
            </x-application.settings-section>

            <x-application.settings-section :title="__('source.self_hosted_advanced')"
                :description="__('source.gitlab_advanced_description')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="htmlUrl"
                        :label="__('source.gitlab_url')" />
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="apiUrl" :label="__('source.api_url')" />
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="customUser"
                        :label="__('source.ssh_user')" />
                    <x-forms.input canGate="update" :canResource="$gitlab_app" type="number" id="customPort"
                        :label="__('source.ssh_port')" />
                    <div class="lg:col-span-2">
                        <x-forms.listbox canGate="update" :canResource="$gitlab_app" id="privateKeyId" :label="__('source.ssh_private_key_optional')"
                            :options="collect($privateKeys)->map(fn ($key) => [
                                'value' => $key->id,
                                'label' => $key->name,
                            ])->prepend(['value' => null, 'label' => __('source.none')])->values()->all()"
                            :disabled="! auth()->user()->can('update', $gitlab_app)" />
                    </div>
                </div>
            </x-application.settings-section>

            <x-application.settings-section :title="__('source.webhook')"
                :description="__('source.gitlab_webhook_description')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <x-forms.input readonly :label="__('source.webhook_url')"
                            value="{{ rtrim($this->resolvePublicBaseUrl(), '/') }}/webhooks/source/gitlab/events" />
                    </div>
                    <x-forms.input canGate="update" :canResource="$gitlab_app" id="webhookToken"
                        :label="__('source.webhook_secret_token')" type="password"
                        :helper="__('source.gitlab_webhook_secret_helper')" />
                </div>
            </x-application.settings-section>
        </form>

        <div x-data="{ search: '' }" class="application-settings-form mt-6">
            <x-application.settings-section :title="__('common.resources')"
                :description="__('source.gitlab_resources_description')" flush>
                @if ($applications->isEmpty())
                    <x-empty :title="__('source.no_resources_use_source')"
                        :description="__('source.gitlab_resources_empty_description')"
                        icon-name="sources" size="sm" />
                @else
                    <div class="border-b border-neutral-200 p-3 dark:border-white/[0.08]">
                        <div class="relative w-full max-w-sm">
                            <x-reicon name="search"
                                class="pointer-events-none absolute top-1/2 left-2.5 z-10 size-3.5 -translate-y-1/2 text-neutral-400 dark:text-fg-faint" />
                            <input x-model.debounce.150ms="search" type="search" :placeholder="__('source.search_resources')"
                                class="h-8! w-full rounded-lg! border-neutral-200! bg-white! py-0! pr-3! pl-8! text-[12px]! shadow-none! placeholder:text-neutral-400 focus:border-accent! focus:ring-0! dark:border-white/[0.08]! dark:bg-white/[0.035]! dark:text-fg! dark:placeholder:text-fg-faint">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <div
                            class="grid min-w-[680px] grid-cols-[minmax(10rem,.8fr)_minmax(10rem,.8fr)_minmax(12rem,1fr)_8rem] border-b border-neutral-200 bg-neutral-50 px-4 py-2.5 text-[11px] font-medium text-neutral-500 dark:border-white/[0.08] dark:bg-white/[0.05] dark:text-fg-faint">
                            <div>{{ __('source.project') }}</div>
                            <div>{{ __('source.environment') }}</div>
                            <div>{{ __('source.resource') }}</div>
                            <div>{{ __('source.repository') }}</div>
                        </div>
                        @foreach ($applications->sortBy('name', SORT_NATURAL) as $application)
                            @php
                                $projectName = (string) data_get($application, 'environment.project.name');
                                $environmentName = (string) data_get($application, 'environment.name');
                                $resourceName = (string) $application->name;
                                $repoLabel = $application->git_repository.':'.$application->git_branch;
                                $searchValue = strtolower(
                                    $projectName.' '.$environmentName.' '.$resourceName.' '.$repoLabel,
                                );
                            @endphp
                            <a {{ wireNavigate() }}
                                href="{{ route('project.application.configuration', [
                                    'project_uuid' => data_get($application, 'environment.project.uuid'),
                                    'environment_uuid' => data_get($application, 'environment.uuid'),
                                    'application_uuid' => data_get($application, 'uuid'),
                                ]) }}"
                                x-show="search === '' || '{{ addslashes($searchValue) }}'.includes(search.toLowerCase())"
                                class="grid min-h-13 min-w-[680px] grid-cols-[minmax(10rem,.8fr)_minmax(10rem,.8fr)_minmax(12rem,1fr)_8rem] items-center border-b border-neutral-200 px-4 py-2.5 text-[12px] transition-colors last:border-b-0 hover:bg-neutral-50 hover:no-underline dark:border-white/[0.07] dark:hover:bg-white/[0.025]">
                                <span class="truncate text-neutral-500 dark:text-fg-dim">{{ $projectName }}</span>
                                <span class="truncate text-neutral-500 dark:text-fg-dim">{{ $environmentName }}</span>
                                <span class="truncate font-medium text-black dark:text-fg">{{ $resourceName }}</span>
                                <span class="truncate text-neutral-500 dark:text-fg-dim">{{ $repoLabel }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-application.settings-section>
        </div>
    @else
        <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="truncate text-[24px]! leading-7! font-semibold! tracking-tight!">
                    {{ $name ?: __('common.gitlab_app') }}
                </h1>
                <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
                    {{ __('source.finish_connecting_gitlab_app') }}
                </p>
            </div>
            @can('delete', $gitlab_app)
                <div class="shrink-0 sm:ml-auto">
                    <x-modal-confirmation :title="__('source.confirm_gitlab_app_deletion')" isErrorButton :buttonTitle="__('common.delete')"
                        submitAction="delete" :actions="[__('source.selected_gitlab_app_deleted')]"
                        confirmationText="{{ data_get($gitlab_app, 'name') }}"
                        :confirmationLabel="__('source.confirm_gitlab_app_name')"
                        :shortConfirmationLabel="__('source.gitlab_app_name')" :confirmWithPassword="false"
                        :step2ButtonText="__('common.permanently_delete_button')" />
                </div>
            @endcan
        </header>

        <div class="application-settings-form flex flex-col gap-6"
            x-data="{
                webhookEndpoint: $wire.entangle('webhook_endpoint').live,
                useCustomWebhookEndpoint: $wire.entangle('use_custom_webhook_endpoint').live,
                customWebhookEndpoint: $wire.entangle('custom_webhook_endpoint').live,
                redirectPath: '/webhooks/source/gitlab/redirect',
                get redirectUri() {
                    const base = (this.useCustomWebhookEndpoint ? this.customWebhookEndpoint : this.webhookEndpoint) || '';
                    return base ? base.replace(/\/+$/, '') + this.redirectPath : '';
                }
            }">
            <x-application.settings-section :title="__('source.step_create_oauth_app')"
                :description="__('source.step_create_oauth_app_description')">
                <div class="flex flex-col gap-3 text-[12px] leading-5 text-neutral-600 dark:text-fg-dim">
                    <a href="{{ rtrim($htmlUrl, '/') }}/-/profile/applications" target="_blank"
                        class="inline-flex w-fit items-center gap-1 font-medium text-black underline-offset-2 hover:underline dark:text-fg">
                        {{ rtrim($htmlUrl, '/') }}/-/profile/applications
                        <x-external-link />
                    </a>
                    <ul class="list-inside list-disc space-y-1.5">
                        <li>
                            {{ __('source.set_redirect_uri_to', ['label' => __('source.redirect_uri')]) }}
                            <code class="rounded bg-neutral-100 px-1.5 py-0.5 text-[11px] dark:bg-white/[0.06]"
                                x-text="redirectUri || @js($redirectUri)">{{ $redirectUri }}</code>
                        </li>
                        <li>
                            {{ __('source.enable_scopes') }} <code class="rounded bg-neutral-100 px-1.5 py-0.5 text-[11px] dark:bg-white/[0.06]">api</code>,
                            <code class="rounded bg-neutral-100 px-1.5 py-0.5 text-[11px] dark:bg-white/[0.06]">read_user</code>,
                            <code class="rounded bg-neutral-100 px-1.5 py-0.5 text-[11px] dark:bg-white/[0.06]">read_repository</code>
                        </li>
                        <li>{{ __('source.uncheck_confidential', ['label' => 'Confidential']) }}</li>
                    </ul>
                </div>
            </x-application.settings-section>

            <form wire:submit="submit" class="contents">
                <x-application.settings-section :title="__('source.step_enter_credentials')"
                    :description="__('source.step_enter_credentials_description')">
                    <x-slot:actions>
                        <x-forms.button type="submit">{{ __('source.save') }}</x-forms.button>
                    </x-slot:actions>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-forms.input id="name" :label="__('common.name')" />
                        <x-forms.input id="clientId" :label="__('source.application_id')" required
                            :helper="__('source.application_id_helper')" />
                        <x-forms.input id="clientSecretInput" :label="__('source.application_secret')" type="password"
                            :required="blank($clientSecretInput) && blank(data_get($gitlab_app, 'client_secret'))"
                            :helper="__('source.application_secret_helper')" />
                        <x-forms.input id="groupName" :label="__('source.group_name')"
                            :helper="__('source.group_name_optional_helper')" />
                    </div>

                    @if (! isCloud() || isDev())
                        <div class="mt-4 grid gap-4 lg:grid-cols-2">
                            <div class="lg:col-span-2 text-[12px] text-neutral-500 dark:text-fg-dim">
                                {{ __('source.gitlab_redirect_explanation') }}
                            </div>
                            <div class="lg:col-span-2 max-w-md">
                                <x-forms.listbox id="use_custom_webhook_endpoint" :label="__('source.webhook_endpoint')"
                                    :live="true" :options="[
                                        ['value' => false, 'label' => __('source.use_instance_endpoint')],
                                        ['value' => true, 'label' => __('source.use_custom_endpoint')],
                                    ]"
                                    x-model="useCustomWebhookEndpoint"
                                    :helper="__('source.custom_endpoint_helper')" />
                            </div>
                            <div class="lg:col-span-2" x-show="!useCustomWebhookEndpoint">
                                <x-forms.listbox id="webhook_endpoint" x-model="webhookEndpoint"
                                    :label="__('source.selected_endpoint')"
                                    :helper="__('source.selected_endpoint_helper')"
                                    :options="collect([$fqdn, $ipv4, $ipv6, config('app.url')])
                                        ->filter()->unique()->map(fn ($endpoint) => [
                                            'value' => $endpoint,
                                            'label' => __('source.use_endpoint', ['endpoint' => $endpoint]),
                                        ])->values()->all()" />
                            </div>
                            <div class="lg:col-span-2" x-cloak x-show="useCustomWebhookEndpoint">
                                <x-forms.input x-model="customWebhookEndpoint" id="custom_webhook_endpoint"
                                    type="url" :label="__('source.custom_endpoint')"
                                    placeholder="https://coolify.example.com"
                                    :helper="__('source.gitlab_custom_endpoint_helper')" />
                            </div>
                        </div>
                    @endif

                    <div class="mt-4" x-data="{ open: false }">
                        <button type="button" @click="open = !open"
                            class="flex w-full items-center justify-between rounded-lg border border-neutral-200 px-3 py-2.5 text-left text-[12px] font-medium text-neutral-700 transition-colors hover:bg-neutral-50 dark:border-white/[0.08] dark:text-fg-dim dark:hover:bg-white/[0.03]">
                            {{ __('source.advanced_self_hosted') }}
                            <svg class="size-3.5 transition-transform" :class="{ 'rotate-180': open }"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div x-cloak x-show="open" x-collapse.duration.200ms
                            class="mt-3 grid gap-4 rounded-lg border border-neutral-200 p-3 lg:grid-cols-2 dark:border-white/[0.08]">
                            <x-forms.input id="htmlUrl" :label="__('source.gitlab_url')"
                                :helper="__('source.gitlab_change_url_helper')" />
                            <x-forms.input id="apiUrl" :label="__('source.api_url')"
                                :helper="__('source.gitlab_api_url_helper')" />
                            <x-forms.input id="customUser" :label="__('source.ssh_user')" />
                            <x-forms.input type="number" id="customPort" :label="__('source.ssh_port')" />
                            @if (! isCloud())
                                <div class="max-w-xs lg:col-span-2">
                                    <x-forms.checkbox :label="__('source.system_wide')" id="isSystemWide"
                                        :helper="__('source.system_wide_gitlab_helper')" />
                                </div>
                            @endif
                        </div>
                    </div>
                </x-application.settings-section>
            </form>

            @if ($clientId)
                <x-application.settings-section :title="__('source.gitlab_authorize')"
                    :description="__('source.gitlab_authorize_description')">
                    <a href="{{ $this->getOAuthUrl() }}" wire:key="oauth-url-{{ md5((string) $redirectUri) }}"
                        class="button button-highlighted">
                        {{ __('source.connect_to_gitlab') }}
                        <x-external-link />
                    </a>
                </x-application.settings-section>
            @endif
        </div>
    @endif
</div>
