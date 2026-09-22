<?php

use App\Livewire\Storage\Create;
use Illuminate\Support\Facades\App;

it('formats the internal target settings route as a clickable link', function () {
    $settingsUrl = route('settings.advanced').'#endpoint-section';
    $exception = new RuntimeException("Private target. Configure allowed internal targets: {$settingsUrl}");
    $method = new ReflectionMethod(Create::class, 'connectionErrorDescription');

    $description = $method->invoke(new Create, $exception);

    expect($description)
        ->toContain('href="'.$settingsUrl.'"')
        ->toContain(__('common.set_here'))
        ->not->toContain('targets: '.$settingsUrl);
});

it('translates the internal target settings link', function () {
    $previousLocale = App::getLocale();
    App::setLocale('zh-cn');

    try {
        $settingsUrl = route('settings.advanced').'#endpoint-section';
        $exception = new RuntimeException("Private target. Configure allowed internal targets: {$settingsUrl}");
        $method = new ReflectionMethod(Create::class, 'connectionErrorDescription');

        $description = $method->invoke(new Create, $exception);

        expect($description)
            ->toContain(__('common.set_here'))
            ->not->toContain('Set them here.');
    } finally {
        App::setLocale($previousLocale);
    }
});
