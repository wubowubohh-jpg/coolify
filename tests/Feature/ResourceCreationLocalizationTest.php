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
