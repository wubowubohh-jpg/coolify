<?php

it('localizes the project, application, and manual server creation flows', function () {
    $projectView = file_get_contents(resource_path('views/livewire/project/add-empty.blade.php'));
    $applicationView = file_get_contents(resource_path('views/livewire/project/new/select.blade.php'));
    $applicationComponent = file_get_contents(app_path('Livewire/Project/New/Select.php'));
    $serverView = file_get_contents(resource_path('views/livewire/server/new/by-ip.blade.php'));

    expect($projectView)
        ->toContain("__('common.project_name_placeholder')")
        ->toContain("__('common.production_environment_created_automatically')")
        ->not->toContain('Your project name')
        ->not->toContain('Create project')
        ->and($applicationView)
        ->toContain("@js(__('common.deploy'))")
        ->not->toContain("'Deploy ' + application.name")
        ->and($applicationComponent)
        ->toContain("__('common.public_git_repository')")
        ->toContain("__('common.docker_image_card_description')")
        ->not->toContain("'name' => 'Public Git Repository'")
        ->and($serverView)
        ->toContain(":title=\"__('common.connect_server')\"")
        ->toContain(":label=\"__('common.dedicated_build_server')\"")
        ->not->toContain('Connect a server')
        ->not->toContain('Use as a dedicated build server');
});

it('localizes the server general settings views', function () {
    $views = [
        resource_path('views/livewire/server/show.blade.php'),
        resource_path('views/livewire/server/partials/localhost-general.blade.php'),
    ];

    foreach ($views as $view) {
        $contents = file_get_contents($view);

        expect($contents)
            ->toContain("__('common.server_overview')")
            ->toContain("__('common.connection')")
            ->toContain("__('common.fetch_server_details')")
            ->toContain("__('common.validate_connection')")
            ->toContain("__('common.connection_timeout')")
            ->toContain("__('common.server_timezone')")
            ->toContain("__('common.wildcard_domain')")
            ->not->toContain('Server overview')
            ->not->toContain('Connection timeout')
            ->not->toContain('Wildcard domain');
    }

    expect(file_get_contents(resource_path('views/livewire/server/partials/server-details.blade.php')))
        ->toContain("__('common.operating_system')")
        ->toContain("__('common.docker_version')")
        ->toContain("__('common.compose_version')")
        ->not->toContain("'Operating system'")
        ->not->toContain("'Docker version'");
});

it('localizes Git source settings and related workflows', function () {
    $views = [
        resource_path('views/livewire/source/github/change.blade.php'),
        resource_path('views/livewire/source/github/create.blade.php'),
        resource_path('views/livewire/source/github/permissions.blade.php'),
        resource_path('views/livewire/source/github/resources.blade.php'),
        resource_path('views/livewire/source/gitlab/change.blade.php'),
        resource_path('views/livewire/source/gitlab/create.blade.php'),
        resource_path('views/source/all.blade.php'),
        resource_path('views/livewire/project/application/source.blade.php'),
        resource_path('views/livewire/project/shared/webhooks.blade.php'),
    ];

    $contents = implode("\n", array_map(fn (string $view): string => file_get_contents($view), $views));

    expect($contents)
        ->toContain("__('source.automated_installation')")
        ->toContain("__('source.manual_installation')")
        ->toContain("__('source.oauth_credentials')")
        ->toContain("__('source.step_create_oauth_app')")
        ->toContain("__('source.manual_git_webhooks')")
        ->not->toContain('>Automated installation<')
        ->not->toContain('>Manual installation<')
        ->not->toContain('>Register with GitHub<')
        ->not->toContain('Repository settings');

    expect(file_get_contents(app_path('Livewire/Source/Github/Change.php')))
        ->toContain("__('source.github_app_updated')")
        ->toContain("__('source.private_key_format_not_supported')")
        ->not->toContain('Github App updated.');

    expect(file_get_contents(app_path('Livewire/Source/Gitlab/Change.php')))
        ->toContain("__('source.gitlab_app_updated')")
        ->toContain("__('source.gitlab_not_connected')")
        ->not->toContain('GitLab App updated.');
});
