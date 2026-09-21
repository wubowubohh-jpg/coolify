<div class="mt-8 flex w-full max-w-none flex-col gap-6 lg:mt-3">
    @if ($current_step === 'private_keys')
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.private_repository') }}</h2>
                    <p>{{ __('common.private_repository_description') }}</p>
                </div>
            </div>
            <div class="application-settings-section-body p-0!">
                @forelse ($private_keys as $key)
                    <button type="button"
                        class="group flex w-full items-center gap-3 border-b border-neutral-200 px-4 py-3 text-left transition-colors last:border-b-0 hover:bg-neutral-50 dark:border-white/[0.06] dark:hover:bg-white/[0.025]"
                        wire:click="setPrivateKey('{{ $key->id }}')"
                        wire:loading.attr="disabled" wire:target="setPrivateKey('{{ $key->id }}')"
                        wire:key="{{ $key->id }}">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-neutral-100 text-neutral-500 dark:bg-white/[0.06] dark:text-fg-dim">
                            <x-reicon name="keys" class="size-4" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-black dark:text-fg">{{ $key->name }}</div>
                            <p class="mt-0.5 truncate text-xs text-neutral-500 dark:text-fg-dim">
                                {{ $key->description ?: __('common.ssh_deploy_key') }}
                            </p>
                        </div>
                    </button>
                @empty
                    <x-empty :title="__('common.no_private_keys')"
                        :description="__('common.private_repository_key_description')"
                        icon-name="keys">
                        <x-slot:contents>
                            <a class="button" href="{{ route('security.private-key.index') }}" {{ wireNavigate() }}>
                                {{ __('common.create_private_key') }}
                            </a>
                        </x-slot:contents>
                    </x-empty>
                @endforelse
            </div>
        </section>
    @endif

    @if ($current_step === 'repository')
        <form wire:submit="submit">
            <section class="application-settings-section">
                <div class="application-settings-section-header">
                    <div>
                        <h2>{{ __('common.repository_configuration') }}</h2>
                        <p>{{ __('common.repository_configuration_description') }}</p>
                    </div>
                    <x-forms.button type="submit" isHighlighted>Continue</x-forms.button>
                </div>
                <div class="application-settings-section-body space-y-5">
                    <x-forms.input id="repository_url" required :label="__('common.repository')"
                        placeholder="git@github.com:owner/repository.git" />
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-forms.input id="branch" required :label="__('common.branch')" />
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
                            <x-forms.input type="number" required id="port" :label="__('common.port')"
                                :readonly="$is_static || $build_pack === 'static'" />
                        @endif
                        @if ($is_static)
                            <x-forms.input id="publish_directory" required :label="__('common.publish_directory')" />
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
