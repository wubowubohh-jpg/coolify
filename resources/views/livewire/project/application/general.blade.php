<div x-data="{
    initLoadingCompose: $wire.entangle('initLoadingCompose'),
    canUpdate: @js(auth()->user()->can('update', $application)),
    shouldDisable() {
        return this.initLoadingCompose || !this.canUpdate;
    }
}">
    <form wire:submit='submit' class="application-settings-form flex flex-col">
        <x-unsaved-bar action="submit"
            targets="name,description,buildPack,staticImage,baseDirectory,dockerComposeLocation,dockerComposeCustomBuildCommand,dockerComposeCustomStartCommand,watchPaths,dockerfileLocation,dockerfileTargetBuild,publishDirectory,installCommand,buildCommand,startCommand,customNginxConfiguration,dockerfile,dockerRegistryImageName,dockerRegistryImageTag,portsExposes,portsMappings,customNetworkAliases,customDockerRunOptions,httpBasicAuthUsername,httpBasicAuthPassword,preDeploymentCommand,preDeploymentCommandContainer,postDeploymentCommand,postDeploymentCommandContainer,isContainerLabelReadonlyEnabled,isContainerLabelEscapeEnabled,customLabels" />
        <div class="application-settings-grid flex flex-col gap-6">
            <x-application.settings-section id="application-details-section" :title="__('common.application_details')" :helper="__('common.application_details_helper')" class="application-details-card">
            @if ($buildPack === 'dockercompose')
                <x-slot:actions>
                    <x-forms.button canGate="update" :canResource="$application" wire:target='initLoadingCompose'
                        x-on:click="$wire.dispatch('loadCompose', false)">
                        {{ $application->docker_compose_raw ? __('common.reload_compose') : __('common.load_compose') }}
                    </x-forms.button>
                </x-slot:actions>
            @endif
            <div class="grid gap-4">
                <x-forms.input x-bind:disabled="shouldDisable()" id="name" :label="__('common.name')" required />
                <x-forms.input x-bind:disabled="shouldDisable()" id="description" :label="__('common.description')" />
            </div>

            </x-application.settings-section>

            <x-application.settings-section id="access-section" :title="__('common.access')" :helper="__('common.access_helper')">
            <section id="public-access-section" @class([
                'border-b border-neutral-200 pb-5 dark:border-white/[0.07]' => $buildPack !== 'dockercompose',
            ])>
            <h3 class="mb-3 text-sm font-semibold text-black dark:text-fg">{{ __('common.public_access') }}</h3>
            @php
                $domainCount = 0;
                $primaryDomain = null;
                if ($buildPack === 'dockercompose') {
                    $composeDomains = $application->docker_compose_domains
                        ? json_decode($application->docker_compose_domains, true)
                        : null;
                    if (is_array($composeDomains)) {
                        foreach ($composeDomains as $serviceDomain) {
                            $domainString = data_get($serviceDomain, 'domain');
                            if (filled($domainString)) {
                                $domainCount += countDomains($domainString);
                                $primaryDomain ??= collect(explode(',', $domainString))
                                    ->map(fn ($domain) => trim($domain))
                                    ->first(fn ($domain) => filled($domain));
                            }
                        }
                    }
                } elseif (filled($fqdn)) {
                    $domainCount = countDomains($fqdn);
                    $primaryDomain = collect(explode(',', $fqdn))
                        ->map(fn ($domain) => trim($domain))
                        ->first(fn ($domain) => filled($domain));
                }
                $additionalDomainCount = max(0, $domainCount - 1);
            @endphp
            @php
                $applicationDomainsUrl = route('project.application.domains', [
                    'project_uuid' => $application->environment->project->uuid,
                    'environment_uuid' => $application->environment->uuid,
                    'application_uuid' => $application->uuid,
                ]);
            @endphp
            <div class="group relative flex items-center gap-3 rounded-lg border border-neutral-200 bg-neutral-50/60 px-4 py-3 transition-colors hover:bg-neutral-100 focus-within:ring-2 focus-within:ring-coollabs/40 dark:border-white/[0.07] dark:bg-white/[0.05] dark:hover:bg-white/[0.08] dark:focus-within:ring-warning/40">
                <a class="flex min-w-0 flex-1 items-center gap-3 after:absolute after:inset-0 after:content-[''] focus-visible:outline-none"
                    :aria-label="$domainCount > 0 ? __('common.manage_application_domains') : __('common.add_application_domain')"
                    href="{{ $applicationDomainsUrl }}" {{ wireNavigate() }}>
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-md bg-neutral-200/70 text-neutral-600 dark:bg-white/[0.07] dark:text-fg-dim">
                        <x-reicon name="globe" class="size-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-black dark:text-fg">
                            @if ($primaryDomain)
                                <span class="block truncate">{{ $primaryDomain }}</span>
                            @else
                                {{ __('common.no_public_domain_configured') }}
                            @endif
                        </p>
                        <p class="text-xs text-neutral-500 dark:text-fg-dim">
                            @if ($additionalDomainCount > 0)
                                {{ trans_choice('common.additional_domains', $additionalDomainCount, ['count' => $additionalDomainCount]) }}
                            @elseif ($domainCount === 0)
                                {{ __('common.make_application_available') }}
                            @else
                                {{ __('common.manage_dns_redirect_settings') }}
                            @endif
                        </p>
                    </div>
                </a>
                <a class="button relative z-10 ml-auto shrink-0" :aria-label="$domainCount > 0 ? __('common.manage_application_domains') : __('common.add_application_domain')"
                    href="{{ $applicationDomainsUrl }}" {{ wireNavigate() }}>
                    {{ $domainCount > 0 ? __('common.manage_domains') : __('common.add_domain') }}
                    <x-reicon name="arrow-right" class="size-4" />
                </a>
            </div>
            </section>

            @if ($buildPack !== 'dockercompose')
                <livewire:project.application.internal-access :application="$application"
                    :key="'application-internal-access-'.$application->id" />
            @endif
            </x-application.settings-section>

            <x-application.settings-section id="build-pipeline-section" :title="__('common.build_pipeline')" :helper="__('common.build_pipeline_helper')">
            @if (!$application->dockerfile && $application->build_pack !== 'dockerimage')
                <div class="application-build-pack-options mb-5 border-b border-neutral-200 pb-5 dark:border-white/[0.07]">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-forms.listbox id="buildPack" :label="__('common.build_strategy')" live :options="[
                            ['value' => 'railpack', 'label' => __('common.railpack')],
                            ['value' => 'nixpacks', 'label' => __('common.nixpacks')],
                            ['value' => 'static', 'label' => __('common.static')],
                            ['value' => 'dockerfile', 'label' => __('common.dockerfile_build_pack')],
                            ['value' => 'dockercompose', 'label' => __('common.docker_compose_build_pack')],
                        ]" x-bind:disabled="shouldDisable()" />
                        @if ($isStatic || $buildPack === 'static')
                            <x-forms.listbox id="staticImage" :label="__('common.web_server')" required :options="[
                                ['value' => 'nginx:alpine', 'label' => 'nginx:alpine'],
                                ['value' => 'apache:alpine', 'label' => 'apache:alpine', 'disabled' => true],
                            ]" x-bind:disabled="!canUpdate" />
                        @endif
                    </div>
                </div>
            @endif
            @if ($application->could_set_build_commands() || ($isStatic && $buildPack !== 'static'))
                <div class="mb-5 w-full border-b border-neutral-200 pb-5 dark:border-white/[0.07]">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-forms.listbox id="siteType" :label="__('common.site_type')" onChange="setSiteType" :options="[
                            ['value' => 'dynamic', 'label' => __('common.dynamic_site')],
                            ['value' => 'static', 'label' => __('common.static')],
                            ['value' => 'spa', 'label' => __('common.single_page_application')],
                        ]"
                            :helper="__('common.site_type_helper')"
                            x-bind:disabled="!canUpdate" />
                    </div>
                </div>
            @endif
            <div class="flex flex-col gap-5">
                @if ($application->build_pack === 'dockerimage')
                    <p class="text-sm text-neutral-500 dark:text-fg-dim">{{ __('common.nothing_to_build') }}</p>
                @else
                    <div class="flex flex-col gap-5">
                        @if ($buildPack === 'dockercompose')
                            <div class="flex flex-col gap-2">
                                <div x-data="{
                                    baseDir: @entangle('baseDirectory'),
                                    composeLocation: @entangle('dockerComposeLocation'),
                                    normalizePath(path) {
                                        if (!path || path.trim() === '') return '/';
                                        path = path.trim();
                                        path = path.replace(/\/+$/, '');
                                        if (!path.startsWith('/')) {
                                            path = '/' + path;
                                        }
                                        return path;
                                    },
                                    normalizeBaseDir() {
                                        this.baseDir = this.normalizePath(this.baseDir);
                                    },
                                    normalizeComposeLocation() {
                                        this.composeLocation = this.normalizePath(this.composeLocation);
                                    }
                                }" class="grid gap-4 lg:grid-cols-2">
                                    <x-forms.input x-bind:disabled="shouldDisable()" placeholder="/"
                                        :label="__('common.base_directory')"
                                        :helper="__('common.base_directory_helper')" x-model="baseDir"
                                        @blur="normalizeBaseDir()" />
                                    <x-forms.input x-bind:disabled="shouldDisable()"
                                        placeholder="/docker-compose.yaml"
                                        :label="__('common.docker_compose_location')"
                                        :helper="__('common.docker_compose_location_helper', ['path' => Str::start($baseDirectory . $dockerComposeLocation, '/')])"
                                        x-model="composeLocation" @blur="normalizeComposeLocation()" />
                                </div>
                                <div class="w-full sm:w-96">
                                    <x-forms.checkbox instantSave id="isPreserveRepositoryEnabled"
                                        :label="__('common.preserve_repository_deployment')"
                                        :helper="__('common.preserve_repository_deployment_helper')"
                                        x-bind:disabled="shouldDisable()" />
                                </div>
                                <div class="grid gap-4 pt-4">
                                        <div class="grid gap-4 lg:grid-cols-2">
                                            <x-forms.input x-bind:disabled="shouldDisable()"
                                                placeholder="docker compose build" id="dockerComposeCustomBuildCommand"
                                                 :helper="__('common.compose_custom_command_helper', ['command' => 'docker compose build'])"
                                                :label="__('common.custom_build_command')" />
                                            <x-forms.input x-bind:disabled="shouldDisable()"
                                                placeholder="docker compose up -d" id="dockerComposeCustomStartCommand"
                                                 :helper="__('common.compose_custom_command_helper', ['command' => 'docker compose up -d'])"
                                                :label="__('common.custom_start_command')" />
                                        </div>
                                        @if ($this->dockerComposeCustomBuildCommand)
                                            <div wire:key="docker-compose-build-preview">
                                                <x-forms.input readonly value="{{ $this->dockerComposeBuildCommandPreview }}"
                                                     :label="__('common.final_build_command_preview')"
                                                     :helper="__('common.final_command_preview_helper')" />
                                            </div>
                                        @endif
                                        @if ($this->dockerComposeCustomStartCommand)
                                            <div wire:key="docker-compose-start-preview">
                                                <x-forms.input readonly value="{{ $this->dockerComposeStartCommandPreview }}"
                                                     :label="__('common.final_start_command_preview')"
                                                     :helper="__('common.final_command_preview_helper')" />
                                            </div>
                                        @endif
                                </div>
                                @if ($this->application->is_github_based() && !$this->application->is_public_repository())
                                    <div class="pt-4">
                                        <x-forms.textarea
                                            :helper="__('common.watch_paths_helper')"
                                            placeholder="services/api/**" id="watchPaths" :label="__('common.watch_paths')"
                                            x-bind:disabled="shouldDisable()" />
                                    </div>
                                @endif
                            </div>
                        @else
                            <div x-data="{
                                baseDir: @entangle('baseDirectory'),
                                dockerfileLocation: @entangle('dockerfileLocation'),
                                normalizePath(path) {
                                    if (!path || path.trim() === '') return '/';
                                    path = path.trim();
                                    path = path.replace(/\/+$/, '');
                                    if (!path.startsWith('/')) {
                                        path = '/' + path;
                                    }
                                    return path;
                                },
                                normalizeBaseDir() {
                                    this.baseDir = this.normalizePath(this.baseDir);
                                },
                                normalizeDockerfileLocation() {
                                    this.dockerfileLocation = this.normalizePath(this.dockerfileLocation);
                                }
                            }" class="grid gap-4 lg:grid-cols-2">
                                <x-forms.input placeholder="/"
                                    :label="__('common.base_directory')" :helper="__('common.base_directory_helper')"
                                    x-bind:disabled="!canUpdate" x-model="baseDir" @blur="normalizeBaseDir()" />
                                @if ($buildPack === 'dockerfile' && !$application->dockerfile)
                                    <x-forms.input placeholder="/Dockerfile"
                                        :label="__('common.dockerfile_location')"
                                        :helper="__('common.dockerfile_location_helper', ['path' => Str::start($application->base_directory . $application->dockerfile_location, '/')])"
                                        x-bind:disabled="!canUpdate" x-model="dockerfileLocation"
                                        @blur="normalizeDockerfileLocation()" />
                                @endif

                                @if ($buildPack === 'dockerfile')
                                    <x-forms.input id="dockerfileTargetBuild" :label="__('common.docker_build_stage_target')"
                                        :helper="__('common.docker_build_stage_target_helper')"
                                        x-bind:disabled="!canUpdate" />
                                @endif
                                @if ($application->could_set_build_commands())
                                    @if ($application->settings->is_static)
                                        <x-forms.input placeholder="/dist" id="publishDirectory"
                                            :label="__('common.publish_directory')" required x-bind:disabled="!canUpdate" />
                                    @else
                                        <x-forms.input placeholder="/" id="publishDirectory"
                                            :label="__('common.publish_directory')" x-bind:disabled="!canUpdate" />
                                    @endif
                                @endif

                            </div>
                            @if ($this->application->is_github_based() && !$this->application->is_public_repository())
                                <div class="pb-4">
                                    <x-forms.textarea
                                         :helper="__('common.watch_paths_helper')"
                                         placeholder="src/pages/**" id="watchPaths" :label="__('common.watch_paths')"
                                        x-bind:disabled="!canUpdate" />
                                </div>
                            @endif
                            @if ($application->could_set_build_commands() && ($buildPack === 'nixpacks' || $buildPack === 'railpack'))
                                <div class="grid gap-4 lg:grid-cols-3">
                                     <x-forms.input :helper="__('common.command_file_helper', ['file' => $buildPack === 'railpack' ? 'railpack.json' : 'nixpacks.toml'])"
                                         id="installCommand" :label="__('common.install_command')" x-bind:disabled="!canUpdate" />
                                     <x-forms.input :helper="__('common.command_file_helper', ['file' => $buildPack === 'railpack' ? 'railpack.json' : 'nixpacks.toml'])"
                                         id="buildCommand" :label="__('common.build_command')" x-bind:disabled="!canUpdate" />
                                     <x-forms.input :helper="__('common.command_file_helper', ['file' => $buildPack === 'railpack' ? 'railpack.json' : 'nixpacks.toml'])"
                                         id="startCommand" :label="__('common.start_command')" x-bind:disabled="!canUpdate" />
                                </div>
                            @endif
                            @if ($buildPack !== 'dockercompose')
                                @php
                                    $hasBuildServers = \App\Models\Server::buildServers(currentTeam()->id)->exists();
                                    $buildServerOptions = [
                                        ['value' => false, 'label' => __('common.deployment_server')],
                                        $hasBuildServers
                                            ? ['value' => true, 'label' => __('common.available_build_server')]
                                            : ['value' => true, 'label' => __('common.no_build_servers_connected'), 'disabled' => true],
                                    ];
                                @endphp
                                <div class="grid gap-4 pt-2 sm:grid-cols-2">
                                    <x-forms.listbox id="isBuildServerEnabled" :label="__('common.builder_selection')"
                                        onChange="instantSave" :options="$buildServerOptions"
                                        :helper="__('common.builder_selection_helper')"
                                        x-bind:disabled="!canUpdate" />
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
            @if ($isStatic || $buildPack === 'static')
                <div class="mt-5 border-t border-neutral-200 pt-5 dark:border-white/[0.07]">
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <label class="flex w-fit items-center gap-1.5" style="margin-bottom: 0">
                            {{ __('common.custom_nginx_configuration') }}
                            <x-helper :helper="__('common.custom_nginx_configuration_helper')" />
                        </label>
                        @can('update', $application)
                            <x-modal-confirmation :title="__('common.confirm_nginx_generation')"
                                :buttonTitle="__('common.generate_default')"
                                submitAction="generateNginxConfiguration('{{ $application->settings->is_spa ? 'spa' : 'static' }}')"
                                :actions="[
                                    __('common.nginx_overwrite_action'),
                                    __('common.nginx_default_action', ['type' => $application->settings->is_spa ? 'SPA' : __('common.static')]),
                                ]" />
                        @endcan
                    </div>
                    <x-forms.textarea id="customNginxConfiguration"
                        :placeholder="__('common.empty_uses_default_configuration')" rows="10"
                        monacoEditorLanguage="nginx" useMonacoEditor x-bind:disabled="!canUpdate" />
                </div>
            @endif
            @if ($buildPack === 'dockercompose')
                <div x-data="{ showRaw: true }" class="mt-5">
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <h3>{{ __('common.docker_compose') }}</h3>
                        <x-forms.button x-show="{{ $application->settings->is_raw_compose_deployment_enabled ? 'false' : 'true' }}"
                            @click.prevent="showRaw = !showRaw"
                            x-text="showRaw ? @js(__('common.show_deployable_compose')) : @js(__('common.show_raw_compose'))"></x-forms.button>
                    </div>
                    @if ($application->settings->is_raw_compose_deployment_enabled)
                        <x-forms.textarea rows="10" readonly id="dockerComposeRaw"
                            :label="__('common.docker_compose_content_application', ['id' => $application->id])"
                            :helper="__('common.modify_compose_file_repository')"
                            monacoEditorLanguage="yaml" useMonacoEditor />
                    @else
                        @if ((int) $application->compose_parsing_version >= 3)
                            <div x-show="showRaw">
                                <x-forms.textarea rows="10" readonly id="dockerComposeRaw"
                                    :label="__('common.docker_compose_content_raw')"
                                    :helper="__('common.modify_compose_file_repository')"
                                    monacoEditorLanguage="yaml" useMonacoEditor />
                            </div>
                        @endif
                        <div x-show="showRaw === false">
                            <x-forms.textarea rows="10" readonly id="dockerCompose"
                                :label="__('common.docker_compose_content')"
                                :helper="__('common.modify_compose_file_repository')"
                                monacoEditorLanguage="yaml" useMonacoEditor />
                        </div>
                    @endif
                    <div class="w-full sm:w-96">
                        <x-forms.checkbox :label="__('common.escape_special_characters_labels')"
                            :helper="__('common.escape_special_characters_helper')"
                            id="isContainerLabelEscapeEnabled" instantSave
                            x-bind:disabled="!canUpdate"></x-forms.checkbox>
                        {{-- <x-forms.checkbox label="Readonly labels"
                            helper="Labels are readonly by default. Readonly means that edits you do to the labels could be lost and Coolify will autogenerate the labels for you. If you want to edit the labels directly, disable this option. <br><br>Be careful, it could break the proxy configuration after you restart the container as Coolify will now NOT autogenerate the labels for you (ofc you can always reset the labels to the coolify defaults manually)."
                            id="isContainerLabelReadonlyEnabled" instantSave></x-forms.checkbox> --}}
                    </div>
                </div>
            @endif
            @if ($application->dockerfile)
                <div class="mt-6">
                    <x-forms.textarea :label="__('common.dockerfile')" id="dockerfile" monacoEditorLanguage="dockerfile"
                        useMonacoEditor rows="6" x-bind:disabled="!canUpdate"> </x-forms.textarea>
                </div>
            @endif
            </x-application.settings-section>
            @if ($buildPack !== 'dockercompose')
                <x-application.settings-section id="container-image-section" :title="__('common.container_image')" :helper="__('common.container_image_helper')">
                @if ($application->destination->server->isSwarm())
                    @if ($application->build_pack !== 'dockerimage')
                        <div>{!! __('common.docker_swarm_registry_info') !!}</div>
                    @endif
                @endif
                <div class="grid gap-4 lg:grid-cols-2">
                    @if ($application->build_pack === 'dockerimage')
                        @if ($application->destination->server->isSwarm())
                            <x-forms.input required id="dockerRegistryImageName" :label="__('common.image')" placeholder="nginx"
                                x-bind:disabled="!canUpdate" />
                            <x-forms.input id="dockerRegistryImageTag" :label="__('common.tag')" placeholder="alpine"
                                :helper="__('common.image_tag_helper')"
                                x-bind:disabled="!canUpdate" />
                        @else
                            <x-forms.input id="dockerRegistryImageName" :label="__('common.image')" placeholder="nginx"
                                x-bind:disabled="!canUpdate" />
                            <x-forms.input id="dockerRegistryImageTag" :label="__('common.tag')" placeholder="alpine"
                                :helper="__('common.image_tag_helper')"
                                x-bind:disabled="!canUpdate" />
                        @endif
                    @else
                        @if (
                            $application->destination->server->isSwarm() ||
                                $application->additional_servers->count() > 0 ||
                                $application->settings->is_build_server_enabled)
                            <x-forms.input id="dockerRegistryImageName" required :label="__('common.image')"
                                placeholder="ghcr.io/your-org/your-app" x-bind:disabled="!canUpdate" />
                            <x-forms.input id="dockerRegistryImageTag"
                                :helper="__('common.registry_image_tag_helper')"
                                placeholder="latest" :label="__('common.tag')"
                                x-bind:disabled="!canUpdate" />
                        @else
                            <x-forms.input id="dockerRegistryImageName"
                                :helper="__('common.registry_image_empty_helper')"
                                placeholder="ghcr.io/your-org/your-app"
                                :label="__('common.image')" x-bind:disabled="!canUpdate" />
                            <x-forms.input id="dockerRegistryImageTag"
                                placeholder="latest"
                                :helper="__('common.registry_image_tag_helper')"
                                :label="__('common.tag')" x-bind:disabled="!canUpdate" />
                        @endif
                    @endif
                </div>
                </x-application.settings-section>
            @endif

            @if ($buildPack !== 'dockercompose')
                @php
                    $applicationDomainsUrl = route('project.application.domains', [
                        'project_uuid' => $application->environment->project->uuid,
                        'environment_uuid' => $application->environment->uuid,
                        'application_uuid' => $application->uuid,
                    ]);
                    $portsExposesDomainHint = __('common.domains_internal_port_hint', ['url' => $applicationDomainsUrl]);
                @endphp
                <x-application.settings-section id="networking-section" :title="__('common.networking')" :helper="__('common.networking_helper')">
                @if ($this->detectedPortInfo)
                    @if ($this->detectedPortInfo['isEmpty'])
                        <div
                            class="flex items-start gap-2 p-4 mb-4 text-sm rounded-lg bg-warning-50 dark:bg-warning-900/20 text-warning-800 dark:text-warning-300 border border-warning-200 dark:border-warning-800">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-semibold">{{ __('common.port_environment_detected', ['port' => $this->detectedPortInfo['port']]) }}</span>
                                <p class="mt-1">{!! __('common.ports_exposes_empty_message', ['port' => $this->detectedPortInfo['port']]) !!}</p>
                            </div>
                        </div>
                    @elseif (!$this->detectedPortInfo['matches'])
                        <div
                            class="flex items-start gap-2 p-4 mb-4 text-sm rounded-lg bg-warning-50 dark:bg-warning-900/20 text-warning-800 dark:text-warning-300 border border-warning-200 dark:border-warning-800">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-semibold">{{ __('common.port_mismatch_detected') }}</span>
                                <p class="mt-1">{!! __('common.port_mismatch_message', ['port' => $this->detectedPortInfo['port']]) !!}</p>
                            </div>
                        </div>
                    @else
                        <div
                            class="flex items-start gap-2 p-4 mb-4 text-sm rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z"
                                    clip-rule="evenodd" />
                            </svg>
                            <div>
                                <span class="font-semibold">{{ __('common.port_environment_configured') }}</span>
                                <p class="mt-1">{!! __('common.port_configured_message', ['port' => $this->detectedPortInfo['port']]) !!}</p>
                            </div>
                        </div>
                    @endif
                @endif
                @if ((empty($portsExposes) || $portsExposes === '0') && !empty($fqdn))
                    <x-callout type="info" :title="__('common.no_ports_exposed')" class="mb-4">
                        {{ __('common.no_ports_exposed_description') }}
                    </x-callout>
                @endif
                <div class="grid gap-4 lg:grid-cols-[14rem_16rem_minmax(0,1fr)]">
                    <div class="min-w-0">
                    @if ($isStatic || $buildPack === 'static')
                        <x-forms.input id="portsExposes" :label="__('common.ports_exposes')" readonly
                            :helper="$portsExposesDomainHint"
                            canGate="update" :canResource="$application"
                            x-bind:disabled="!canUpdate" />
                    @else
                        @if ($application->settings->is_container_label_readonly_enabled === false)
                            <x-forms.input placeholder="3000,3001" id="portsExposes" :label="__('common.ports_exposes')" readonly
                                :helper="__('common.ports_exposes_readonly_helper', ['domains' => $portsExposesDomainHint])"
                                canGate="update" :canResource="$application"
                                x-bind:disabled="!canUpdate" />
                        @else
                            <x-forms.input placeholder="3000,3001" id="portsExposes" :label="__('common.ports_exposes')"
                                :helper="__('common.ports_exposes_helper', ['domains' => $portsExposesDomainHint])"
                                canGate="update" :canResource="$application"
                                x-bind:disabled="!canUpdate" />
                        @endif
                    @endif
                    <p class="mt-1.5 text-xs text-neutral-500 dark:text-fg-dim">
                        {!! $portsExposesDomainHint !!}
                    </p>
                    </div>
                    @if (!$application->destination->server->isSwarm())
                        <x-forms.input placeholder="3000:3000" id="portsMappings" :label="__('common.port_mappings')"
                            :helper="__('common.port_mappings_application_helper')"
                            x-bind:disabled="!canUpdate" />
                    @endif
                    @if (!$application->destination->server->isSwarm())
                        <x-forms.input id="customNetworkAliases" :label="__('common.network_aliases')"
                            :helper="__('common.network_aliases_helper')"
                            wire:model="customNetworkAliases" x-bind:disabled="!canUpdate" />
                    @endif
                </div>
                </x-application.settings-section>

                <x-application.settings-section id="runtime-section" :title="__('common.runtime')" :helper="__('common.runtime_helper')">
                    <x-forms.input
                        :helper="__('common.custom_docker_options_helper')"
                        placeholder="--cap-add SYS_ADMIN --device=/dev/fuse --security-opt apparmor:unconfined --ulimit nofile=1024:1024 --tmpfs /run:rw,noexec,nosuid,size=65536k --hostname=myapp"
                        id="customDockerRunOptions" :label="__('common.custom_docker_options')" x-bind:disabled="!canUpdate" />
                </x-application.settings-section>

                <x-application.settings-section id="security-section" :title="__('common.security')" :helper="__('common.security_helper')">
                    @if ($application->settings->is_container_label_readonly_enabled == false)
                    <x-empty size="sm" :title="__('common.authentication_managed_labels')"
                        :description="__('common.authentication_managed_labels_description')"
                        icon-name="admin">
                        <x-slot:contents>
                            <button type="button" class="button"
                                @click="window.scrollToSettingsSection?.('container-labels-section')">
                                {{ __('common.go_to_container_labels') }}
                            </button>
                        </x-slot:contents>
                    </x-empty>
                    @else
                    <x-forms.listbox id="isHttpBasicAuthEnabled" :label="__('common.authentication')" onChange="instantSave"
                        :helper="__('common.http_basic_auth_helper')"
                        :options="[
                            ['value' => false, 'label' => __('common.none')],
                            ['value' => true, 'label' => __('common.http_basic_authentication')],
                        ]" x-bind:disabled="!canUpdate" />
                    @if ($isHttpBasicAuthEnabled)
                        <div class="mt-5 grid w-full gap-4 border-t border-neutral-200 pt-5 sm:grid-cols-2 dark:border-white/[0.07]">
                            <x-forms.input id="httpBasicAuthUsername" :label="__('common.username')" required
                                x-bind:disabled="!canUpdate" />
                            <x-forms.input id="httpBasicAuthPassword" type="password" :label="__('common.password')" required
                                x-bind:disabled="!canUpdate" />
                        </div>
                    @endif
                    @endif
                </x-application.settings-section>
            @endif

            <x-application.settings-section id="deployment-lifecycle-section" :title="__('common.deployment_lifecycle')" :helper="__('common.deployment_lifecycle_helper')">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="flex flex-col gap-4">
                    <x-forms.input x-bind:disabled="shouldDisable()" placeholder="php artisan migrate"
                        id="preDeploymentCommand" :label="__('common.pre_deployment')"
                        :helper="__('common.pre_deployment_command_helper')" />
                    @if ($buildPack === 'dockercompose')
                        <x-forms.input x-bind:disabled="shouldDisable()" id="preDeploymentCommandContainer"
                            :label="__('common.container_name')"
                            :helper="__('common.container_name_helper')" />
                    @endif
                </div>
                <div class="flex flex-col gap-4">
                    <x-forms.input x-bind:disabled="shouldDisable()" placeholder="php artisan migrate"
                        id="postDeploymentCommand" :label="__('common.post_deployment')"
                        :helper="__('common.post_deployment_command_helper')" />
                    @if ($buildPack === 'dockercompose')
                        <x-forms.input x-bind:disabled="shouldDisable()" id="postDeploymentCommandContainer"
                            :label="__('common.container_name')"
                            :helper="__('common.container_name_helper')" />
                    @endif
                </div>
            </div>
            </x-application.settings-section>

            @if ($buildPack !== 'dockercompose')
                <x-application.settings-section id="container-labels-section" :title="__('common.container_labels')" :helper="__('common.container_labels_helper')">
                <div class="grid w-full gap-4 sm:grid-cols-2">
                    <x-forms.listbox id="isContainerLabelReadonlyEnabled" :label="__('common.label_management')"
                        onChange="instantSave"
                        :helper="__('common.label_management_helper')"
                        :options="[
                            ['value' => true, 'label' => __('common.managed_labels_auto')],
                            ['value' => false, 'label' => __('common.managed_labels_manual')],
                        ]" x-bind:disabled="!canUpdate" />
                    <x-forms.listbox id="isContainerLabelEscapeEnabled" :label="__('common.special_characters')"
                        onChange="instantSave"
                        :helper="__('common.escape_special_characters_helper')"
                        :options="[
                            ['value' => true, 'label' => __('common.escape_special_characters_enabled')],
                            ['value' => false, 'label' => __('common.escape_special_characters_disabled')],
                        ]" x-bind:disabled="!canUpdate" />
                </div>
                <div class="mt-5 border-t border-neutral-200 pt-5 dark:border-white/[0.07]">
                    <div class="mb-1.5 flex items-center justify-between gap-3">
                        <label class="flex w-fit items-center gap-1.5" style="margin-bottom: 0">{{ __('common.active_labels') }}</label>
                        @can('update', $application)
                            <x-modal-confirmation :title="__('common.confirm_labels_reset')"
                                :buttonTitle="__('common.reset_to_defaults')" submitAction="resetDefaultLabels(true)"
                                :actions="[
                                    __('common.custom_proxy_labels_lost'),
                                    __('common.proxy_labels_reset'),
                                ]" confirmationText="{{ $application->fqdn . '/' }}"
                                :confirmationLabel="__('common.confirm_application_url')"
                                :shortConfirmationLabel="__('common.application_url')" :confirmWithPassword="false"
                                :step2ButtonText="__('common.permanently_reset_labels')" />
                        @endcan
                    </div>
                    @if ($application->settings->is_container_label_readonly_enabled)
                        <x-forms.textarea readonly disabled rows="15" id="customLabels"
                            monacoEditorLanguage="ini" useMonacoEditor x-bind:disabled="!canUpdate"></x-forms.textarea>
                    @else
                        <x-forms.textarea rows="15" id="customLabels"
                            monacoEditorLanguage="ini" useMonacoEditor x-bind:disabled="!canUpdate"></x-forms.textarea>
                    @endif
                </div>
                </x-application.settings-section>
            @endif
        </div>
    </form>

    <x-domain-conflict-modal :conflicts="$domainConflicts" :showModal="$showDomainConflictModal" confirmAction="confirmDomainUsage" />

    @script
        <script>
            $wire.$on('loadCompose', (isInit = true) => {
                // Only load compose file if user has permission (this event should only be dispatched when authorized)
                $wire.initLoadingCompose = true;
                $wire.loadComposeFile(isInit);
            });
        </script>
    @endscript
</div>
