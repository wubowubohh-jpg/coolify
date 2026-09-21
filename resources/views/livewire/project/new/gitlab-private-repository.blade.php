<div class="mt-8 flex w-full max-w-none flex-col gap-6 lg:mt-3">
    @if ($gitlab_apps->isEmpty())
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.gitlab_app') }}</h2>
                    <p>{{ __('common.git_app_selection_description') }}</p>
                </div>
            </div>
            <div class="application-settings-section-body">
                <x-empty :title="__('common.no_gitlab_apps')"
                    :description="__('common.create_app_access_repositories')"
                    icon-name="sources">
                    <x-slot:contents>
                        <x-modal-input :buttonTitle="__('common.add_gitlab_app')" :title="__('common.new_gitlab_app')" closeOutside="false">
                            <livewire:source.gitlab.create />
                        </x-modal-input>
                    </x-slot:contents>
                </x-empty>
            </div>
        </section>
    @elseif ($current_step === 'gitlab_apps')
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.choose_gitlab_app') }}</h2>
                    <p>{{ __('common.git_app_selection_description') }}</p>
                </div>
                <x-modal-input :buttonTitle="__('common.add_gitlab_app')" :title="__('common.new_gitlab_app')" closeOutside="false">
                    <livewire:source.gitlab.create />
                </x-modal-input>
            </div>
            <div class="application-settings-section-body p-0!">
                @foreach ($gitlab_apps as $glapp)
                    <button type="button"
                        class="group flex w-full items-center gap-3 border-b border-neutral-200 px-4 py-3 text-left transition-colors last:border-b-0 hover:bg-neutral-50 dark:border-white/[0.06] dark:hover:bg-white/[0.025]"
                        wire:click.prevent="loadRepositories({{ $glapp->id }})"
                        wire:loading.attr="disabled" wire:target="loadRepositories({{ $glapp->id }})"
                        wire:key="{{ $glapp->id }}">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-neutral-500 dark:bg-white/[0.06] dark:text-fg-dim">
                            <x-git-icon class="size-4" git="App\Models\GitlabApp" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-black dark:text-fg">
                                {{ data_get($glapp, 'name') }}
                            </div>
                            <p class="mt-0.5 truncate text-xs text-neutral-500 dark:text-fg-dim">
                                {{ data_get($glapp, 'html_url') }}
                            </p>
                        </div>
                        <x-loading wire:loading wire:target="loadRepositories({{ $glapp->id }})" />
                    </button>
                @endforeach
            </div>
        </section>
    @elseif ($current_step === 'repository')
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.choose_repository') }}</h2>
                    <p>{{ __('common.gitlab_repository_description') }}</p>
                </div>
                @if ($gitlab_app_id)
                    <x-forms.button wire:click.prevent="loadRepositories({{ $gitlab_app_id }})">
                        {{ __('common.refresh') }}
                    </x-forms.button>
                @endif
            </div>
            <div class="application-settings-section-body">
                @if ($repositories->isNotEmpty())
                    <div class="flex items-end gap-2">
                        <x-forms.datalist class="w-full" :label="__('common.repository')"
                            :placeholder="__('common.search_repositories')" wire:model.live="selected_project_id">
                            @foreach ($repositories as $repo)
                                <option value="{{ data_get($repo, 'id') }}">
                                    {{ data_get($repo, 'path_with_namespace') }}
                                </option>
                            @endforeach
                        </x-forms.datalist>
                        <x-forms.button :showLoadingIndicator="false" wire:click.prevent="loadBranches"
                            wire:target="loadBranches,selected_project_id">
                            <x-loading-on-button wire:loading.delay
                                wire:target="loadBranches,selected_project_id" />
                            {{ __('common.load_repository') }}
                        </x-forms.button>
                    </div>
                @else
                    <x-empty size="sm" :title="__('common.no_repositories_available')"
                        :description="__('common.review_gitlab_app')" />
                @endif
            </div>
        </section>

        @if ($branches->isNotEmpty())
            <form wire:submit="submit">
                <section class="application-settings-section">
                    <div class="application-settings-section-header">
                        <div>
                        <h2>{{ __('common.build_configuration') }}</h2>
                        <p>{{ __('common.build_strategy_description') }}</p>
                        </div>
                        <x-forms.button type="submit" isHighlighted>Continue</x-forms.button>
                    </div>
                    <div class="application-settings-section-body space-y-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <x-forms.listbox id="selected_branch_name" :label="__('common.branch')" required
                                :options="$branches->map(fn ($branch) => [
                                    'value' => data_get($branch, 'name'),
                                    'label' => data_get($branch, 'name'),
                                ])->values()->all()" />
                            <x-forms.listbox id="build_pack" :label="__('common.build_pack')" required live :options="[
                                ['value' => 'railpack', 'label' => __('common.railpack')],
                                ['value' => 'nixpacks', 'label' => __('common.nixpacks')],
                                ['value' => 'static', 'label' => __('common.static')],
                                ['value' => 'dockerfile', 'label' => __('common.dockerfile_build_pack')],
                                ['value' => 'dockercompose', 'label' => __('common.docker_compose_build_pack')],
                            ]" />
                            @if ($show_is_static)
                                <x-forms.listbox id="is_static" :label="__('common.output_type')" onChange="instantSave"
                                    :options="[
                                        ['value' => false, 'label' => __('common.web_application')],
                                        ['value' => true, 'label' => __('common.static_site')],
                                    ]" />
                                <x-forms.input type="number" id="port" :label="__('common.port')"
                                    :readonly="$is_static || $build_pack === 'static'"
                                    :helper="__('common.port_helper')" />
                            @endif
                            @if ($is_static)
                                <x-forms.input id="publish_directory" :label="__('common.publish_directory')"
                                    :helper="__('common.static_assets_helper')" />
                            @endif
                        </div>

                        @if ($build_pack === 'dockercompose')
                            <div x-data="{
                                baseDir: @js($base_directory),
                                composeLocation: @js($docker_compose_location),
                                normalize(path) {
                                    if (!path || path.trim() === '') return '/';
                                    const normalized = path.trim().replace(/\/+$/, '');
                                    return normalized.startsWith('/') ? normalized : '/' + normalized;
                                },
                            }" class="grid gap-4 sm:grid-cols-2">
                                <x-forms.input placeholder="/" wire:model.defer="base_directory"
                                    :label="__('common.base_directory')" :helper="__('common.base_directory_helper')"
                                    x-model="baseDir" @blur="baseDir = normalize(baseDir)" />
                                <x-forms.input placeholder="/docker-compose.yaml"
                                    wire:model.defer="docker_compose_location" :label="__('common.compose_file')"
                                    :helper="__('common.compose_file_helper')" x-model="composeLocation"
                                    @blur="composeLocation = normalize(composeLocation)" />
                                <p class="sm:col-span-2 text-xs text-neutral-500 dark:text-fg-dim">
                                    {{ __('common.resolved_file') }}:
                                    <code class="font-mono text-coollabs dark:text-warning"
                                        x-text='(baseDir === "/" ? "" : baseDir) + (composeLocation.startsWith("/") ? composeLocation : "/" + composeLocation)'></code>
                                </p>
                            </div>
                        @else
                            <x-forms.input wire:model="base_directory" :label="__('common.base_directory')"
                                :helper="__('common.base_directory_helper')" />
                        @endif
                    </div>
                </section>
            </form>
        @endif
    @endif
</div>
