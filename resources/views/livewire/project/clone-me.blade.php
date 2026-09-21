<div>
    <x-slot:title>{{ data_get_str($project, 'name')->limit(10) }} > {{ __('common.clone') }} | Coolify</x-slot>
    <div class="w-full max-w-none">
        <header class="mb-5">
            <h1 class="truncate text-[24px]! leading-7! font-semibold! tracking-tight!">{{ $environment->name }}</h1>
            <p class="mt-1 text-[13px] text-neutral-500 dark:text-fg-dim">
                {{ __('common.clone_inside_project', ['project' => $project->name]) }}
            </p>
        </header>

        <div class="flex flex-col gap-6">
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.clone_environment') }}</h2>
                    <p>{{ __('common.copy_resources_to_new_scope', ['environment' => $environment->name]) }}</p>
                </div>
            </div>
            <div class="application-settings-section-body">
                <div class="max-w-md">
                    <x-forms.input required id="newName" :label="__('common.new_name')" />
                </div>
            </div>
        </section>

        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.destination') }}</h2>
                    <p>{{ __('common.choose_clone_destination') }}</p>
                </div>
            </div>
            <div class="application-settings-section-body p-0!">
                @php
                    $destinationCount = $servers->sum(
                        fn ($server) => $server->destinations()->count()
                    );
                @endphp
                <div class="data-table">
                    <div class="data-table-header clone-destinations-table-grid">
                        <span><span class="sr-only">{{ __('common.selected') }}</span></span>
                        <span>{{ __('common.server') }}</span>
                        <span>{{ __('common.network') }}</span>
                    </div>
                    @foreach ($servers->sortBy('id') as $server)
                        @foreach ($server->destinations() as $destination)
                            <button type="button"
                                wire:click="selectServer('{{ $server->id }}', '{{ $destination->uuid }}')"
                                @class([
                                    'data-table-row clone-destinations-table-grid w-full border-b border-neutral-200 text-left last:border-b-0 dark:border-white/[0.06]',
                                    'bg-coollabs/5 dark:bg-warning/[0.06]' => $selectedDestination === $destination->uuid,
                                ])>
                                <span @class([
                                    'flex size-4 items-center justify-center rounded-full border',
                                    'border-coollabs bg-coollabs text-white dark:border-warning dark:bg-warning dark:text-black' => $selectedDestination === $destination->uuid,
                                    'border-neutral-300 dark:border-white/[0.15]' => $selectedDestination !== $destination->uuid,
                                ])>
                                    @if ($selectedDestination === $destination->uuid)
                                        <span class="size-1.5 rounded-full bg-current"></span>
                                    @endif
                                </span>
                                <span class="truncate text-[12px] font-semibold text-black dark:text-fg">
                                    {{ $server->name }}
                                </span>
                                <span class="truncate font-mono text-[11px] text-neutral-600 dark:text-fg-dim">
                                    {{ $destination->name }}
                                </span>
                            </button>
                        @endforeach
                    @endforeach
                    <div
                        class="flex min-h-11 items-center border-t border-neutral-200 px-4 text-[11px] text-neutral-500 dark:border-white/[0.08] dark:text-fg-faint">
                        {{ $destinationCount }} {{ trans_choice('common.destination_count', $destinationCount) }}
                    </div>
                </div>
            </div>
        </section>

        <section class="application-settings-section">
            @php
                $resourceCount = $environment->applications->count()
                    + $environment->databases()->count()
                    + $environment->services->count();
            @endphp
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.resources') }}</h2>
                    <p>{{ $resourceCount }} {{ trans_choice('common.resources_count', $resourceCount) }} {{ __('common.will_be_cloned') }}</p>
                </div>
            </div>
            <div class="application-settings-section-body p-0!">
                <div class="data-table">
                    <div class="data-table-header clone-resources-table-grid">
                        <span>{{ __('common.name') }}</span>
                        <span>{{ __('common.type') }}</span>
                        <span>{{ __('common.description') }}</span>
                    </div>
                    @foreach ($environment->applications->sortBy('name') as $application)
                        <div
                            class="data-table-row clone-resources-table-grid border-b border-neutral-200 last:border-b-0 dark:border-white/[0.06]">
                            <div class="truncate text-[12px] font-semibold text-black dark:text-fg">
                                {{ $application->name }}
                            </div>
                            <div><x-status-badge :status="__('common.application')" type="neutral" /></div>
                            <div class="truncate text-[11px] text-neutral-600 dark:text-fg-dim">
                                {{ $application->description ?: '-' }}
                            </div>
                        </div>
                    @endforeach
                    @foreach ($environment->databases()->sortBy('name') as $database)
                        <div
                            class="data-table-row clone-resources-table-grid border-b border-neutral-200 last:border-b-0 dark:border-white/[0.06]">
                            <div class="truncate text-[12px] font-semibold text-black dark:text-fg">
                                {{ $database->name }}
                            </div>
                            <div><x-status-badge :status="__('common.database')" type="neutral" /></div>
                            <div class="truncate text-[11px] text-neutral-600 dark:text-fg-dim">
                                {{ $database->description ?: '-' }}
                            </div>
                        </div>
                    @endforeach
                    @foreach ($environment->services->sortBy('name') as $service)
                        <div
                            class="data-table-row clone-resources-table-grid border-b border-neutral-200 last:border-b-0 dark:border-white/[0.06]">
                            <div class="truncate text-[12px] font-semibold text-black dark:text-fg">
                                {{ $service->name }}
                            </div>
                            <div><x-status-badge :status="__('common.service')" type="neutral" /></div>
                            <div class="truncate text-[11px] text-neutral-600 dark:text-fg-dim">
                                {{ $service->description ?: '-' }}
                            </div>
                        </div>
                    @endforeach
                    <div
                        class="flex min-h-11 items-center border-t border-neutral-200 px-4 text-[11px] text-neutral-500 dark:border-white/[0.08] dark:text-fg-faint">
                        {{ $resourceCount }} {{ trans_choice('common.resources_count', $resourceCount) }}
                    </div>
                </div>
                <div
                    class="flex flex-col gap-2 border-t border-neutral-200 p-4 sm:flex-row sm:justify-end dark:border-white/[0.06]">
                    <x-forms.button isHighlighted wire:click="clone('environment')"
                        :disabled="! filled($selectedDestination)">
                        {{ __('common.clone_to_environment') }}
                    </x-forms.button>
                    <x-forms.button isHighlighted wire:click="clone('project')"
                        :disabled="! filled($selectedDestination)">
                        {{ __('common.clone_to_project') }}
                    </x-forms.button>
                </div>
            </div>
        </section>
        </div>
    </div>
</div>
