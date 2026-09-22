<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

it('renders a trailing validation error URL as a link', function () {
    $settingsUrl = route('settings.advanced').'#endpoint-section';
    $errors = new ViewErrorBag;
    $errors->put('default', new MessageBag([
        'endpoint' => "Local or private IP addresses are not allowed. Configure allowed internal targets: {$settingsUrl}",
    ]));
    View::share('errors', $errors);

    $html = Blade::render('<x-forms.input id="endpoint" />');

    expect($html)
        ->toContain('href="'.$settingsUrl.'"')
        ->toContain('Set them here');
});

it('uses translated validation link copy in the shared input component', function () {
    $view = file_get_contents(resource_path('views/components/forms/input.blade.php'));

    expect($view)
        ->toContain("{{ __('common.set_here') }}")
        ->not->toContain('>Set them here.</a>');
});

it('uses explicit validation handling in the domain input component', function () {
    $view = file_get_contents(resource_path('views/components/forms/domain-input.blade.php'));

    expect($view)
        ->toContain('@if ($errors->has($domainErrorKey))')
        ->toContain('$message = $errors->first($domainErrorKey);')
        ->not->toContain('@error(')
        ->not->toContain('@enderror');
});
