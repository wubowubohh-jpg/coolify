<div x-data x-init="$nextTick(() => { if ($refs.autofocusInput) $refs.autofocusInput.focus(); })"
    class="mt-8 flex w-full max-w-none flex-col gap-6 lg:mt-3">
    <form wire:submit="loadBranch">
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.public_git_repository') }}</h2>
                    <p>{{ __('common.public_git_repository_description') }}</p>
                </div>
            </div>
            <div class="application-settings-section-body">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <x-forms.input required id="repository_url" :label="__('common.repository_url')"
                            helper="{!! __('repository.url') !!}" placeholder="https://github.com/owner/repository"
                            autofocus />
                    </div>
                    <x-forms.button type="submit" class="w-full justify-center sm:w-auto"
                        wire:loading.attr="disabled" wire:target="loadBranch" :showLoadingIndicator="false">
                        <svg wire:loading wire:target="loadBranch" class="size-3.5 shrink-0 animate-spin"
                            viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor"
                                stroke-width="3" />
                            <path class="opacity-75" d="M21 12a9 9 0 0 0-9-9" stroke="currentColor"
                                stroke-width="3" stroke-linecap="round" />
                        </svg>
                        {{ __('common.check_repository') }}
                    </x-forms.button>
                </div>
                <p class="mt-2 text-xs text-neutral-500 dark:text-fg-dim">
                    {{ __('common.need_sample_browse') }}
                    <a class="font-medium text-coollabs hover:underline dark:text-warning"
                        href="https://github.com/coollabsio/coolify-examples/" target="_blank">
                        {{ __('common.coolify_examples') }}
                    </a>.
                </p>
            </div>
        </section>
    </form>

    @if ($branchFound)
        <form wire:submit="submit">
            <section class="application-settings-section">
                <div class="application-settings-section-header">
                    <div>
                        <h2>{{ __('common.build_configuration') }}</h2>
                        <p>{{ __('common.build_configuration_description') }}</p>
                    </div>
                    <x-forms.button type="submit" isHighlighted>{{ __('common.continue') }}</x-forms.button>
                </div>
                <div class="application-settings-section-body space-y-5">
                    @if ($rate_limit_remaining && $rate_limit_reset)
                        <x-callout type="info" :title="__('common.git_provider_rate_limit')">
                            {{ __('common.rate_limit_remaining', ['count' => $rate_limit_remaining]) }}
                            {{ __('common.rate_limit_reset_at', ['time' => $rate_limit_reset]) }}
                        </x-callout>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-forms.input id="git_branch" :label="__('common.branch')"
                            :disabled="$git_source !== 'other'"
                            :helper="__('common.branch_after_creation')" />
                        <x-forms.listbox id="build_pack" :label="__('common.build_pack')" required live :options="[
                            ['value' => 'railpack', 'label' => __('common.railpack')],
                            ['value' => 'nixpacks', 'label' => __('common.nixpacks')],
                            ['value' => 'static', 'label' => __('common.static')],
                            ['value' => 'dockerfile', 'label' => __('common.dockerfile_build_pack')],
                            ['value' => 'dockercompose', 'label' => __('common.docker_compose_build_pack')],
                        ]" />
                        @if ($show_is_static)
                            <x-forms.listbox id="isStatic" :label="__('common.output_type')" onChange="instantSave"
                                :options="[
                                    ['value' => false, 'label' => __('common.web_application')],
                                    ['value' => true, 'label' => __('common.static_site')],
                                ]" />
                            <x-forms.input type="number" id="port" :label="__('common.port')"
                                :readonly="$isStatic || $build_pack === 'static'"
                                :helper="__('common.port_helper')" />
                        @endif
                        @if ($isStatic)
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
</div>
