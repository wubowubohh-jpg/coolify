<div>
    @php
        $canUpdate = auth()->user()->can('update', $application);
        $labelsManagedByCoolify = $application->settings->is_container_label_readonly_enabled;
        // Use model UUIDs: Livewire update requests do not carry page route params.
        $generalRouteParameters = [
            'project_uuid' => data_get($application, 'environment.project.uuid'),
            'environment_uuid' => data_get($application, 'environment.uuid'),
            'application_uuid' => $application->uuid,
        ];
    @endphp

    <div class="flex flex-col gap-6">
        <x-application.settings-section id="advanced-build-section" :title="__('common.build_section')"
            :helper="__('common.build_section_helper')">
            <div class="grid w-full gap-4 sm:grid-cols-2">
                <x-forms.listbox id="disableBuildCache" :label="__('common.build_cache')" onChange="instantSave"
                    :helper="__('common.build_cache_helper')"
                    :options="[
                        ['value' => false, 'label' => __('common.use_docker_build_cache')],
                        ['value' => true, 'label' => __('common.rebuild_from_scratch')],
                    ]" :disabled="! $canUpdate" />
                <x-forms.listbox id="injectBuildArgsToDockerfile" :label="__('common.build_arguments')" onChange="instantSave"
                    :helper="__('common.inject_build_args_helper')"
                    :options="[
                        ['value' => true, 'label' => __('common.inject_build_args_auto')],
                        ['value' => false, 'label' => __('common.managed_dockerfile')],
                    ]" :disabled="! $canUpdate" />
                <x-forms.listbox id="includeSourceCommitInBuild" :label="__('common.source_commit_availability')" onChange="instantSave"
                    :helper="__('common.source_commit_helper')"
                    :options="[
                        ['value' => false, 'label' => __('common.runtime_only_preserves_cache')],
                        ['value' => true, 'label' => __('common.available_during_build')],
                    ]" :disabled="! $canUpdate" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="advanced-container-section" :title="__('common.container_section')"
            :helper="__('common.container_section_helper')">
            <div class="grid w-full gap-4 sm:grid-cols-2">
                <x-forms.listbox id="isConsistentContainerNameEnabled" :label="__('common.container_naming')" onChange="instantSave"
                    :helper="__('common.consistent_name_helper', ['uuid' => $application->uuid])"
                    :options="[
                        ['value' => false, 'label' => __('common.generated_name_rolling')],
                        ['value' => true, 'label' => __('common.consistent_name_no_rolling')],
                    ]" :disabled="! $canUpdate" />
                @if ($isConsistentContainerNameEnabled === true)
                    <x-forms.input
                        :helper="__('common.custom_name_helper')"
                        id="customInternalName" :label="__('common.custom_container_name')" canGate="update"
                        wire:change="saveCustomName" :canResource="$application" />
                @endif
            </div>
        </x-application.settings-section>

        @if ($application->git_based())
            <x-application.settings-section id="advanced-deployment-section" :title="__('common.deployment')"
                :helper="__('common.deployment_section_helper')">
                <div class="grid w-full gap-4 sm:grid-cols-2">
                    <x-forms.listbox id="isAutoDeployEnabled" :label="__('common.auto_deploy')" onChange="instantSave"
                        :helper="__('common.auto_deploy_helper')"
                        :options="[
                            ['value' => true, 'label' => __('common.deploy_on_push')],
                            ['value' => false, 'label' => __('common.manual_deployments_only')],
                        ]" :disabled="! $canUpdate" />
                </div>
            </x-application.settings-section>

            <x-application.settings-section id="advanced-git-section" :title="__('common.git')"
                :helper="__('common.git_options_helper')">
                <div class="grid w-full gap-4 sm:grid-cols-2">
                    <x-forms.listbox id="isGitSubmodulesEnabled" :label="__('common.submodules')" onChange="instantSave"
                        :helper="__('common.allow_git_submodules')"
                        :options="[
                            ['value' => true, 'label' => __('common.clone_submodules')],
                            ['value' => false, 'label' => __('common.skip_submodules')],
                        ]" :disabled="! $canUpdate" />
                    <x-forms.listbox id="isGitLfsEnabled" :label="__('common.git_lfs')" onChange="instantSave"
                        :helper="__('common.git_lfs_helper')"
                        :options="[
                            ['value' => true, 'label' => 'Enabled'],
                            ['value' => false, 'label' => 'Disabled'],
                        ]" :disabled="! $canUpdate" />
                    <x-forms.listbox id="isGitShallowCloneEnabled" :label="__('common.clone_depth')" onChange="instantSave"
                        :helper="__('common.shallow_clone_helper')"
                        :options="[
                            ['value' => false, 'label' => __('common.full_history')],
                            ['value' => true, 'label' => __('common.shallow_clone_latest')],
                        ]" :disabled="! $canUpdate" />
                </div>
            </x-application.settings-section>
        @endif

        @if ($application->build_pack === 'dockercompose')
            <x-application.settings-section id="advanced-compose-section" :title="__('common.docker_compose')"
                :helper="__('common.compose_section_helper')">
                <div class="grid w-full gap-4 sm:grid-cols-2">
                    <x-forms.listbox id="isRawComposeDeploymentEnabled" :label="__('common.compose_deployment')" onChange="instantSave"
                        :helper="__('common.raw_compose_helper')"
                        :options="[
                            ['value' => false, 'label' => __('common.managed_by_coolify')],
                            ['value' => true, 'label' => __('common.raw_deploy_as_is')],
                        ]" :disabled="! $canUpdate" />
                    <x-forms.listbox id="isConnectToDockerNetworkEnabled" :label="__('common.predefined_network')" onChange="instantSave"
                        :helper="__('common.predefined_network_helper')"
                        :options="[
                            ['value' => false, 'label' => __('common.isolated_network_only')],
                            ['value' => true, 'label' => __('common.connect_predefined_network')],
                        ]" :disabled="! $canUpdate" />
                </div>
            </x-application.settings-section>
        @endif

        <x-application.settings-section id="advanced-proxy-section" :title="__('common.proxy')"
            :helper="__('common.proxy_section_helper')">
            @if ($labelsManagedByCoolify)
                <div class="grid w-full gap-4 sm:grid-cols-2">
                    <x-forms.listbox id="isGzipEnabled" :label="__('common.gzip_compression')" onChange="instantSave"
                        :helper="__('common.gzip_helper')"
                        :options="[
                            ['value' => true, 'label' => 'Enabled'],
                            ['value' => false, 'label' => 'Disabled'],
                        ]" :disabled="! $canUpdate" />
                    <x-forms.listbox id="isStripprefixEnabled" :label="__('common.path_prefixes')" onChange="instantSave"
                        :helper="__('common.path_prefix_helper')"
                        :options="[
                            ['value' => true, 'label' => __('common.strip_prefixes')],
                            ['value' => false, 'label' => __('common.keep_paths_as_is')],
                        ]" :disabled="! $canUpdate" />
                </div>
            @else
                <x-empty size="sm" :title="__('common.proxy_labels_managed')"
                    :description="__('common.proxy_labels_description')"
                    icon-name="globe">
                    <x-slot:contents>
                        <a class="button"
                            href="{{ route('project.application.configuration', $generalRouteParameters) }}#container-labels-section"
                            {{ wireNavigate() }}>
                            {{ __('common.go_container_labels') }}
                        </a>
                    </x-slot:contents>
                </x-empty>
            @endif
        </x-application.settings-section>

        <x-application.settings-section id="advanced-operations-section" :title="__('common.operations')"
            :helper="__('common.operations_section_helper')">
            <div class="grid w-full gap-4 lg:grid-cols-2">
                <x-forms.input type="number" id="stopGracePeriod" :label="__('common.stop_grace_period')"
                    placeholder="{{ DEFAULT_STOP_GRACE_PERIOD_SECONDS }}" wire:change="saveStopGracePeriod"
                    :helper="__('common.stop_grace_period_helper', ['default' => DEFAULT_STOP_GRACE_PERIOD_SECONDS, 'min' => MIN_STOP_GRACE_PERIOD_SECONDS, 'max' => MAX_STOP_GRACE_PERIOD_SECONDS])"
                    min="{{ MIN_STOP_GRACE_PERIOD_SECONDS }}" max="{{ MAX_STOP_GRACE_PERIOD_SECONDS }}"
                    canGate="update" :canResource="$application" />
                <x-forms.input type="number" min="0" id="maxRestartCount" :label="__('common.max_restart_count')"
                    wire:change="saveMaxRestartCount"
                    :helper="__('common.max_restart_count_helper')"
                    canGate="update" :canResource="$application" />
            </div>
        </x-application.settings-section>

        <x-application.settings-section id="advanced-logs-section" :title="__('common.logs')"
            :helper="__('common.logs_section_helper')">
            <div class="grid w-full gap-4 sm:grid-cols-2">
                <x-forms.listbox id="isLogDrainEnabled" :label="__('common.log_drain')" onChange="instantSave"
                    :helper="__('common.log_drain_helper')"
                    :options="[
                        ['value' => false, 'label' => 'Disabled'],
                        ['value' => true, 'label' => __('common.send_logs_to_drain')],
                    ]" :disabled="! $canUpdate" />
            </div>
        </x-application.settings-section>

        @if ($application->build_pack !== 'dockercompose')
            <x-application.settings-section id="advanced-gpu-section" :title="__('common.gpu')"
                :helper="__('common.gpu_section_helper')">
                <div class="grid w-full gap-4 sm:grid-cols-2">
                    <x-forms.listbox id="isGpuEnabled" :label="__('common.gpu_access')" onChange="instantSave"
                        :options="[
                            ['value' => false, 'label' => 'Disabled'],
                            ['value' => true, 'label' => 'Enabled'],
                        ]" :disabled="! $canUpdate" />
                </div>
                @if ($isGpuEnabled)
                    <form id="gpu-settings-form" wire:submit="submit"
                        class="mt-5 flex w-full flex-col gap-4 border-t border-neutral-200 pt-5 dark:border-white/[0.07]">
                        {{-- Scope to GPU form fields; sibling instantSave listboxes share this component. --}}
                        <x-unsaved-bar action="submit"
                            targets="gpuDriver,gpuCount,gpuDeviceIds,gpuOptions" />
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.input :label="__('common.gpu_driver')" id="gpuDriver" canGate="update" :canResource="$application" />
                            <x-forms.input :label="__('common.gpu_count')" :placeholder="__('common.gpu_count_placeholder')" id="gpuCount"
                                canGate="update" :canResource="$application" />
                        </div>
                        <x-forms.input :label="__('common.gpu_device_ids')" placeholder="0,2"
                            :helper="__('common.gpu_device_ids_helper')"
                            id="gpuDeviceIds" canGate="update" :canResource="$application" />
                        <x-forms.textarea rows="6" :label="__('common.gpu_options')" id="gpuOptions" canGate="update"
                            :canResource="$application" />
                    </form>
                @endif
            </x-application.settings-section>
        @endif
    </div>
</div>
