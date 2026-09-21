@php
    $team = auth()->user()?->currentTeam();
    $projectUuid = request()->route('project_uuid');
    $environmentUuid = request()->route('environment_uuid');
    $projects = $projectUuid && $team ? $team->projects()->get() : collect();
    $currentProject = $projectUuid ? $projects->firstWhere('uuid', $projectUuid) : null;
    $environments = $currentProject ? $currentProject->environments()->get() : collect();
    $currentEnvironment = $environmentUuid ? $environments->firstWhere('uuid', $environmentUuid) : null;
    $projectDestinationRoute = request()->routeIs('shared-variables.project.*')
        ? 'shared-variables.project.show'
        : 'project.show';
    $applicationUuid = request()->route('application_uuid');
    $currentApplication = $currentEnvironment && $applicationUuid
        ? $currentEnvironment->applications()->where('uuid', $applicationUuid)->first()
        : null;
    $databaseUuid = request()->route('database_uuid');
    $currentDatabase = $currentEnvironment && $databaseUuid
        ? $currentEnvironment->databases()->firstWhere('uuid', $databaseUuid)
        : null;
    $serviceUuid = request()->route('service_uuid');
    $currentService = $currentEnvironment && $serviceUuid
        ? $currentEnvironment->services()->where('uuid', $serviceUuid)->first()
        : null;
    $currentResource = $currentApplication ?? $currentDatabase ?? $currentService;
    $resourceItems = $currentEnvironment
        ? collect()
            ->concat($currentEnvironment->applications->map(fn ($application) => [
                'type' => 'application',
                'resource' => $application,
            ]))
            ->concat($currentEnvironment->databases()->map(fn ($database) => [
                'type' => 'database',
                'resource' => $database,
            ]))
            ->concat($currentEnvironment->services->map(fn ($service) => [
                'type' => 'service',
                'resource' => $service,
            ]))
            ->sortBy(fn ($item) => strtolower($item['resource']->name))
            ->map(fn ($item) => [
                'label' => $item['resource']->name,
                'href' => match ($item['type']) {
                    'application' => route('project.application.configuration', [
                        'project_uuid' => $currentProject->uuid,
                        'environment_uuid' => $currentEnvironment->uuid,
                        'application_uuid' => $item['resource']->uuid,
                    ]),
                    'database' => route('project.database.configuration', [
                        'project_uuid' => $currentProject->uuid,
                        'environment_uuid' => $currentEnvironment->uuid,
                        'database_uuid' => $item['resource']->uuid,
                    ]),
                    'service' => route('project.service.configuration', [
                        'project_uuid' => $currentProject->uuid,
                        'environment_uuid' => $currentEnvironment->uuid,
                        'service_uuid' => $item['resource']->uuid,
                    ]),
                },
                'active' => $item['resource']->uuid === $currentResource?->uuid,
            ])
            ->values()
        : collect();
    $storageUuid = request()->route('storage_uuid');
    $storages = $storageUuid && $team ? \App\Models\S3Storage::ownedByCurrentTeam()->orderBy('name')->get() : collect();
    $currentStorage = $storages->firstWhere('uuid', $storageUuid);
    $githubAppUuid = request()->route('github_app_uuid');
    $gitlabAppUuid = request()->route('gitlab_app_uuid');
    $sourceUuid = $githubAppUuid ?? $gitlabAppUuid;
    $sources = $sourceUuid && $team ? $team->sources()->sortBy('name')->values() : collect();
    $currentSource = $sources->firstWhere('uuid', $sourceUuid);
    $destinationUuid = request()->route('destination_uuid');
    $destinations = $destinationUuid && $team
        ? \App\Models\Server::isUsable()
            ->with(['standaloneDockers', 'swarmDockers'])
            ->get()
            ->flatMap(fn ($server) => $server->standaloneDockers->concat($server->swarmDockers))
            ->sortBy('name')
            ->values()
        : collect();
    $currentDestination = $destinations->firstWhere('uuid', $destinationUuid);
    $tagName = request()->route('tagName');
    $tags = $tagName && $team
        ? \App\Models\Tag::ownedByCurrentTeam()->orderBy('name')->get()->unique('name')->values()
        : collect();
    $currentTag = $tags->firstWhere('name', $tagName);
    $dashboardContext = match (true) {
        request()->routeIs('dashboard') => 'nav.dashboard',
        request()->routeIs('project.index') => 'nav.projects',
        request()->routeIs('terminal') => 'nav.terminal',
        request()->routeIs('server.*') => 'nav.servers',
        request()->routeIs('source.*') => 'nav.sources',
        request()->routeIs('destination.*') => 'nav.destinations',
        request()->routeIs('storage.*') => 'nav.s3_storage',
        request()->routeIs('shared-variables.*') => 'nav.shared_variables',
        request()->routeIs('team.*') => 'nav.team',
        request()->routeIs('notifications.*') => 'nav.notifications',
        request()->routeIs('security.*') => 'nav.keys_tokens',
        request()->routeIs('tags.*') => 'nav.tags',
        request()->routeIs('settings.*') => 'nav.settings',
        request()->routeIs('profile*') => 'nav.profile',
        request()->routeIs('admin.*') => 'nav.admin',
        default => null,
    };
    // Workspace destinations require an active plan on cloud; unsubscribed users
    // only keep profile/appearance and must not see this switcher.
    $canUseWorkspaceNav = isSubscribed() || ! isCloud();
    $pageDestinations = $canUseWorkspaceNav
        ? collect([
            ['label' => 'nav.dashboard', 'href' => url('/')],
            ['label' => 'nav.projects', 'href' => url('/projects')],
            auth()->user()?->can('canAccessTerminal')
                ? ['label' => 'nav.terminal', 'href' => route('terminal')]
                : null,
            ['label' => 'nav.servers', 'href' => url('/servers')],
            ['label' => 'nav.sources', 'href' => route('source.all')],
            ['label' => 'nav.destinations', 'href' => route('destination.index')],
            ['label' => 'nav.s3_storage', 'href' => route('storage.index')],
            ['label' => 'nav.shared_variables', 'href' => route('shared-variables.index')],
            ['label' => 'nav.team', 'href' => route('team.index')],
            ['label' => 'nav.notifications', 'href' => route('notifications.email')],
            ['label' => 'nav.keys_tokens', 'href' => route('security.private-key.index')],
            ['label' => 'nav.tags', 'href' => route('tags.show')],
            isInstanceAdmin()
                ? ['label' => 'nav.settings', 'href' => route('settings.index')]
                : null,
        ])->filter()
        : collect();
@endphp
<div class="flex min-w-0 items-center gap-0.5 text-[13px]">
    {{-- Team --}}
    <div class="shrink-0" x-data="{ collapsed: false }">
        <livewire:switch-team />
    </div>

    @if (!$currentProject && $dashboardContext && $canUseWorkspaceNav)
        <span class="shrink-0 px-0.5 text-neutral-300 dark:text-fg-faint">/</span>
        <div class="relative min-w-0 shrink" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" @click.outside="open = false" title="{{ __('nav.switch_page') }}"
                class="flex h-8 min-w-0 items-center gap-1.5 rounded-md px-2 opacity-70 transition-[background-color,opacity] hover:bg-neutral-100 hover:opacity-100 dark:hover:bg-white/[0.05]">
                <span class="min-w-0 truncate font-semibold text-black dark:text-fg">{{ __($dashboardContext) }}</span>
                <svg class="size-4 shrink-0 text-neutral-400 dark:text-fg-faint" viewBox="0 0 24 24"
                    fill="none">
                    <path d="M8 9l4-4 4 4M8 15l4 4 4-4" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition.opacity.duration.120ms
                class="listbox-panel scrollbar left-0! z-[90]! max-h-80! min-w-52">
                <div class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">
                    {{ __('nav.pages') }}
                </div>
                @foreach ($pageDestinations as $destination)
                    <a href="{{ $destination['href'] }}" {{ wireNavigate() }} @click="open = false"
                        class="listbox-option {{ $destination['label'] === $dashboardContext ? 'bg-neutral-100 font-medium text-black dark:bg-white/[0.07] dark:text-fg' : '' }}">
                        <span class="min-w-0 flex-1 truncate">{{ __($destination['label']) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @elseif (!$currentProject && $dashboardContext)
        {{-- Static context label only — no links to paid workspace pages. --}}
        <span class="shrink-0 px-0.5 text-neutral-300 dark:text-fg-faint">/</span>
        <span
            class="flex h-8 min-w-0 items-center truncate px-2 font-semibold text-black opacity-70 dark:text-fg">{{ __($dashboardContext) }}</span>
    @endif

    @if ($currentStorage)
        <x-breadcrumb-switcher :title="__('nav.s3_storage')" :label="$currentStorage->name" :items="$storages->map(fn ($storage) => [
            'label' => $storage->name,
            'href' => route('storage.show', ['storage_uuid' => $storage->uuid]),
            'active' => $storage->uuid === $currentStorage->uuid,
        ])">
            <x-slot:meta>
                <span class="inline-flex h-[22px] shrink-0 items-center gap-1.5 rounded-full border border-neutral-200 bg-neutral-100 px-2.5 text-xs font-medium text-black dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-fg"
                    x-data="{ usable: @js((bool) $currentStorage->is_usable) }"
                    @storage-status-changed.window="usable = $event.detail.isUsable">
                    <span class="size-1.5 rounded-full" :class="usable ? 'bg-[#3fb950]' : 'bg-red-500'"></span>
                    <span x-text="usable ? @js(__('nav.connected')) : @js(__('nav.not_usable'))"></span>
                </span>
            </x-slot:meta>
        </x-breadcrumb-switcher>
    @endif

    @if ($currentSource)
        @php
            $sourceConnected = $currentSource instanceof \App\Models\GithubApp
                ? filled($currentSource->installation_id)
                : filled($currentSource->access_token);
        @endphp
        <x-breadcrumb-switcher :title="__('nav.sources')" :label="$currentSource->name ?: __('nav.source')" :items="$sources->map(fn ($source) => [
            'label' => $source->name ?: class_basename($source),
            'href' => $source instanceof \App\Models\GithubApp
                ? route('source.github.show', ['github_app_uuid' => $source->uuid])
                : route('source.gitlab.show', ['gitlab_app_uuid' => $source->uuid]),
            'active' => $source->getMorphClass() === $currentSource->getMorphClass() && $source->uuid === $currentSource->uuid,
        ])">
            <x-slot:meta>
                <span class="inline-flex h-[22px] shrink-0 items-center gap-1.5 rounded-full border border-neutral-200 bg-neutral-100 px-2.5 text-xs font-medium text-black dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-fg">
                <span @class([
                    'size-1.5 rounded-full',
                    'bg-[#3fb950]' => $sourceConnected,
                    'bg-warning' => ! $sourceConnected,
                ])></span>
                    {{ $sourceConnected ? __('nav.connected') : __('nav.setup_incomplete') }}
                </span>
            </x-slot:meta>
        </x-breadcrumb-switcher>
    @endif

    @if ($currentDestination)
        <x-breadcrumb-switcher :title="__('nav.destinations')" :label="$currentDestination->name" :items="$destinations->map(fn ($destination) => [
            'label' => $destination->name,
            'href' => route('destination.show', ['destination_uuid' => $destination->uuid]),
            'active' => $destination->getMorphClass() === $currentDestination->getMorphClass() && $destination->uuid === $currentDestination->uuid,
        ])">
            <x-slot:meta>
                <span class="inline-flex h-[22px] shrink-0 items-center gap-1.5 rounded-full border border-neutral-200 bg-neutral-100 px-2.5 text-xs font-medium text-black dark:border-white/[0.12] dark:bg-white/[0.08] dark:text-fg">
                <span @class([
                    'size-1.5 rounded-full',
                    'bg-[#3fb950]' => $currentDestination->getMorphClass() === 'App\\Models\\StandaloneDocker',
                    'bg-warning' => $currentDestination->getMorphClass() !== 'App\\Models\\StandaloneDocker',
                ])></span>
                    {{ $currentDestination->getMorphClass() === 'App\\Models\\StandaloneDocker' ? __('nav.docker') : __('nav.deprecated') }}
                </span>
            </x-slot:meta>
        </x-breadcrumb-switcher>
    @endif

    @if ($currentTag)
        <x-breadcrumb-switcher :title="__('nav.tags')" :label="$currentTag->name" :items="collect([[
            'label' => __('nav.all_tags'),
            'href' => route('tags.show'),
            'active' => false,
        ]])->concat($tags->map(fn ($tag) => [
            'label' => $tag->name,
            'href' => route('tags.show', ['tagName' => $tag->name]),
            'active' => $tag->name === $currentTag->name,
        ]))" />
    @endif

    @if ($currentProject)
        <span class="shrink-0 text-neutral-300 dark:text-fg-faint px-0.5">/</span>
        {{-- Project switcher --}}
        <div class="relative min-w-0 shrink" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" @click.outside="open = false" title="{{ __('nav.switch_project') }}"
                class="flex items-center gap-1.5 min-w-0 h-8 px-2 rounded-md opacity-70 transition-[background-color,opacity] hover:opacity-100 hover:bg-neutral-100 dark:hover:bg-white/[0.05]">
                <span class="min-w-0 truncate font-semibold text-black dark:text-fg">{{ $currentProject->name }}</span>
                <svg class="size-4 shrink-0 text-neutral-400 dark:text-fg-faint" viewBox="0 0 24 24" fill="none">
                    <path d="M8 9l4-4 4 4M8 15l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition.opacity.duration.120ms
                class="listbox-panel scrollbar left-0! z-[90]! max-h-80! min-w-56 max-w-72">
                <div class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">
                    {{ __('nav.projects') }}
                </div>
                @foreach ($projects as $p)
                    <a href="{{ route($projectDestinationRoute, ['project_uuid' => $p->uuid]) }}" {{ wireNavigate() }} @click="open = false"
                        class="listbox-option {{ $p->uuid === $currentProject->uuid ? 'bg-neutral-100 font-medium text-black dark:bg-white/[0.07] dark:text-fg' : '' }}">
                        <span class="min-w-0 flex-1 truncate">{{ $p->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($currentProject && $currentEnvironment)
        <span class="shrink-0 text-neutral-300 dark:text-fg-faint px-0.5">/</span>
        {{-- Environment switcher --}}
        <div class="relative min-w-0 shrink" x-data="{ open: false }" @keydown.escape.window="open = false">
            <button type="button" @click="open = !open" @click.outside="open = false" title="{{ __('nav.switch_environment') }}"
                class="flex items-center gap-1.5 min-w-0 h-8 px-2 rounded-md opacity-70 transition-[background-color,opacity] hover:opacity-100 hover:bg-neutral-100 dark:hover:bg-white/[0.05]">
                <span class="min-w-0 truncate font-semibold text-black dark:text-fg">{{ $currentEnvironment->name }}</span>
                <svg class="size-4 shrink-0 text-neutral-400 dark:text-fg-faint" viewBox="0 0 24 24" fill="none">
                    <path d="M8 9l4-4 4 4M8 15l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition.opacity.duration.120ms
                class="listbox-panel scrollbar left-0! z-[90]! max-h-80! min-w-52 max-w-72">
                <div class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-neutral-400 dark:text-fg-faint">
                    {{ __('nav.environments') }}
                </div>
                @foreach ($environments as $env)
                    <a href="{{ route('project.resource.index', ['project_uuid' => $currentProject->uuid, 'environment_uuid' => $env->uuid]) }}" {{ wireNavigate() }} @click="open = false"
                        class="listbox-option {{ $env->uuid === $currentEnvironment->uuid ? 'bg-neutral-100 font-medium text-black dark:bg-white/[0.07] dark:text-fg' : '' }}">
                        <span class="min-w-0 flex-1 truncate">{{ $env->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if ($currentResource)
        <x-breadcrumb-switcher :title="__('nav.resources')" :label="$currentResource->name" :items="$resourceItems">
            <x-slot:meta>
                @if ($currentApplication)
                    <livewire:project.application.status :application="$currentApplication"
                        :wire:key="'application-status-'.$currentApplication->uuid" />
                @elseif ($currentDatabase)
                    <livewire:project.database.status :database="$currentDatabase"
                        :wire:key="'database-status-'.$currentDatabase->uuid" />
                @else
                    <livewire:project.service.status :service="$currentService"
                        :wire:key="'service-status-'.$currentService->uuid" />
                @endif
            </x-slot:meta>
        </x-breadcrumb-switcher>
    @endif
</div>
