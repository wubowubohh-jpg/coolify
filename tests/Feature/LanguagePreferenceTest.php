<?php

use App\Http\Middleware\SetLocale;
use App\Livewire\Profile\Appearance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('en');
});

afterEach(function () {
    App::setLocale('en');
});

it('persists the selected locale on the account and redirects back to appearance', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Appearance::class)
        ->call('setLocale', 'zh-cn')
        ->assertRedirect(route('profile.appearance'));

    expect($user->fresh()->locale)->toBe('zh-cn')
        ->and(App::getLocale())->toBe('zh-cn');
});

it('rejects unsupported locales', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Appearance::class)
        ->call('setLocale', 'fr')
        ->assertHasErrors(['locale' => 'in']);

    expect($user->fresh()->locale)->toBeNull();
});

it('uses the account locale before the browser cookie', function () {
    $user = User::factory()->create(['locale' => 'zh-cn']);
    $request = Request::create('/');
    $request->cookies->set('coolify_locale', 'en');
    $request->setUserResolver(fn () => $user);

    app(SetLocale::class)->handle($request, fn () => response('ok'));

    expect(App::getLocale())->toBe('zh-cn');
});

it('uses the browser cookie when the account has no locale preference', function () {
    $request = Request::create('/');
    $request->cookies->set('coolify_locale', 'zh-cn');

    app(SetLocale::class)->handle($request, fn () => response('ok'));

    expect(App::getLocale())->toBe('zh-cn');
});

it('uses the browser cookie when the account locale is no longer supported', function () {
    $user = User::factory()->create(['locale' => 'fr']);
    $request = Request::create('/');
    $request->cookies->set('coolify_locale', 'zh-cn');
    $request->setUserResolver(fn () => $user);

    app(SetLocale::class)->handle($request, fn () => response('ok'));

    expect(App::getLocale())->toBe('zh-cn');
});

it('exposes both supported locales in the appearance settings', function () {
    $appearance = file_get_contents(resource_path('views/components/theme-controls.blade.php'));
    $application = file_get_contents(config_path('app.php'));

    expect($appearance)
        ->toContain('wire:click="setLocale')
        ->toContain("config('app.supported_locales', [])")
        ->toContain("__('settings.language')")
        ->toContain("__('settings.language.description')");

    expect($application)
        ->toContain("'en' => 'settings.language.english'")
        ->toContain("'zh-cn' => 'settings.language.chinese'");

    expect(file_get_contents(resource_path('views/livewire/profile/appearance.blade.php')))
        ->toContain('<x-theme-controls variant="full" :locale="$locale" />');
});

it('does not contain corrupted placeholder text in simplified Chinese translations', function () {
    $translations = json_decode(
        file_get_contents(lang_path('zh-cn.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    $corruptedKeys = collect($translations)
        ->filter(fn (mixed $value): bool => is_string($value) && preg_match('/\?{2,}/', $value) === 1)
        ->keys()
        ->all();

    expect($corruptedKeys)->toBe([]);
});

it('keeps supported translation keys unique and aligned', function () {
    $translations = [];

    foreach (['en', 'zh-cn'] as $locale) {
        $contents = file_get_contents(lang_path("{$locale}.json"));
        preg_match_all('/^\s*,?\s*"([^"\\]+)"\s*:/m', $contents, $matches);

        expect($matches[1])->toHaveCount(count(array_unique($matches[1])));

        $translations[$locale] = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    expect(array_keys($translations['en']))
        ->toEqual(array_keys($translations['zh-cn']));
});

it('uses translation keys across the shared frontend shell', function () {
    $views = [
        resource_path('views/components/navbar.blade.php'),
        resource_path('views/components/top-user-menu.blade.php'),
        resource_path('views/components/top-breadcrumb.blade.php'),
        resource_path('views/components/theme-controls.blade.php'),
        resource_path('views/components/unsaved-bar.blade.php'),
        resource_path('views/components/error-page.blade.php'),
        resource_path('views/components/forms/domain-chips.blade.php'),
        resource_path('views/components/table/filter.blade.php'),
        resource_path('views/components/server/advanced.blade.php'),
        resource_path('views/components/deployment/configuration-diff.blade.php'),
        resource_path('views/components/limit-reached.blade.php'),
        resource_path('views/components/modal.blade.php'),
        resource_path('views/components/resources/breadcrumbs.blade.php'),
        resource_path('views/auth/login.blade.php'),
        resource_path('views/livewire/profile/index.blade.php'),
        resource_path('views/livewire/settings/index.blade.php'),
    ];

    $content = implode("\n", array_map('file_get_contents', $views));

    expect($content)
        ->toContain("__('nav.dashboard')")
        ->toContain("__('appearance.color_theme')")
        ->toContain("__('auth.sign_in_description')")
        ->toContain("__('profile.profile_details')")
        ->toContain("__('settings.instance_settings')")
        ->not->toContain('>Dashboard<')
        ->not->toContain('>Profile details<');
});

it('uses translation keys in shared controls and onboarding', function () {
    $views = [
        resource_path('views/components/applications/deploy.blade.php'),
        resource_path('views/components/configuration-warning.blade.php'),
        resource_path('views/components/confirm-modal.blade.php'),
        resource_path('views/components/database-status-info.blade.php'),
        resource_path('views/livewire/boarding/index.blade.php'),
        resource_path('views/livewire/dashboard.blade.php'),
        resource_path('views/livewire/global-search.blade.php'),
        resource_path('views/livewire/project/index.blade.php'),
        resource_path('views/livewire/server/index.blade.php'),
        resource_path('views/livewire/terminal/index.blade.php'),
        resource_path('views/livewire/destination/index.blade.php'),
        resource_path('views/source/all.blade.php'),
        resource_path('views/livewire/admin/index.blade.php'),
        resource_path('views/livewire/help.blade.php'),
        resource_path('views/livewire/project/show.blade.php'),
        resource_path('views/livewire/project/clone-me.blade.php'),
        resource_path('views/livewire/project/application/advanced.blade.php'),
        resource_path('views/livewire/project/application/heading.blade.php'),
        resource_path('views/livewire/project/application/internal-access.blade.php'),
        resource_path('views/livewire/project/application/source.blade.php'),
        resource_path('views/livewire/project/application/deployment/index.blade.php'),
        resource_path('views/livewire/security/api-tokens.blade.php'),
        resource_path('views/livewire/security/cloud-provider-tokens.blade.php'),
        resource_path('views/livewire/security/cloud-init-scripts.blade.php'),
        resource_path('views/livewire/server/cloudflare-tunnel.blade.php'),
        resource_path('views/livewire/project/database/backup/index.blade.php'),
        resource_path('views/livewire/project/database/backup-edit/general.blade.php'),
        resource_path('views/livewire/project/database/scheduled-backups.blade.php'),
        resource_path('views/livewire/navbar-delete-team.blade.php'),
        resource_path('views/livewire/project/resource/index.blade.php'),
        resource_path('views/livewire/server/security/patches.blade.php'),
        resource_path('views/components/notification/event-grid.blade.php'),
        resource_path('views/livewire/project/new/docker-image.blade.php'),
        resource_path('views/livewire/project/new/docker-compose.blade.php'),
        resource_path('views/livewire/project/new/simple-dockerfile.blade.php'),
        resource_path('views/livewire/project/new/public-git-repository.blade.php'),
        resource_path('views/livewire/project/new/github-private-repository.blade.php'),
        resource_path('views/livewire/project/new/github-private-repository-deploy-key.blade.php'),
        resource_path('views/livewire/project/new/gitlab-private-repository.blade.php'),
        resource_path('views/livewire/project/new/select.blade.php'),
        resource_path('views/livewire/project/application/domains.blade.php'),
        resource_path('views/livewire/project/application/preview-domains.blade.php'),
        resource_path('views/livewire/project/application/partials/domain-row.blade.php'),
        resource_path('views/livewire/project/service/domains.blade.php'),
        resource_path('views/livewire/project/service/partials/domain-table.blade.php'),
        resource_path('views/livewire/storage/index.blade.php'),
        resource_path('views/livewire/storage/resources.blade.php'),
        resource_path('views/livewire/storage/show.blade.php'),
        resource_path('views/livewire/project/new/empty-project.blade.php'),
        resource_path('views/livewire/server/navbar.blade.php'),
        resource_path('views/livewire/server/proxy.blade.php'),
        resource_path('views/livewire/server/proxy/dynamic-configurations.blade.php'),
        resource_path('views/livewire/server/proxy/dynamic-configuration-navbar.blade.php'),
        resource_path('views/livewire/server/proxy/new-dynamic-configuration.blade.php'),
        resource_path('views/livewire/server/proxy/logs.blade.php'),
        resource_path('views/livewire/server/proxy/show.blade.php'),
        resource_path('views/livewire/server/log-drains.blade.php'),
        resource_path('views/livewire/server/sentinel.blade.php'),
        resource_path('views/livewire/server/sentinel/logs.blade.php'),
        resource_path('views/livewire/server/charts.blade.php'),
        resource_path('views/livewire/server/transfer.blade.php'),
        resource_path('views/livewire/server/transfer-import.blade.php'),
        resource_path('views/livewire/server/resources.blade.php'),
        resource_path('views/livewire/project/database/postgresql/general.blade.php'),
        resource_path('views/livewire/project/service/index.blade.php'),
    ];

    $content = implode("\n", array_map('file_get_contents', $views));

    expect($content)
        ->toContain("__('common.deploy')")
        ->toContain("__('common.latest_configuration_not_applied')")
        ->toContain("__('common.confirm')")
        ->toContain("__('common.ssl_configuration')")
        ->toContain("__('onboarding.welcome')")
        ->toContain("__('common.dashboard')")
        ->toContain("__('common.search_everything')")
        ->toContain("__('common.no_matching_projects')")
        ->toContain("__('common.no_matching_servers')")
        ->toContain("__('common.start_terminal_session')")
        ->toContain("__('common.destinations')")
        ->toContain("__('common.sources')")
        ->toContain("__('common.user_lookup')")
        ->toContain("__('common.clone_environment')")
        ->toContain("__('common.deployment_history')")
        ->toContain("__('common.api_tokens')")
        ->toContain("__('common.cloudflare_tunnel')")
        ->toContain("__('common.database_backups')")
        ->toContain("__('common.build_section')")
        ->toContain("__('common.unsaved_changes')")
        ->toContain("__('common.go_back')")
        ->toContain("__('common.domain_chips_helper')")
        ->toContain("__('common.reset_filters')")
        ->toContain("__('common.advanced')")
        ->toContain("__('common.confirm_team_deletion')")
        ->toContain("__('common.creation_limit_reached')")
        ->toContain("__('common.upgrade_subscription_to_create')")
        ->toContain("__('common.toggle_full_value')")
        ->toContain("__('common.create_edit')")
        ->toContain("__('common.no_matching_resources')")
        ->toContain("__('common.resource_types')")
        ->toContain("__('common.server_patching')")
        ->toContain("__('common.package_updates')")
        ->toContain("__('common.notification_events')")
        ->toContain("__('common.create_application')")
        ->toContain("__('common.create_service')")
        ->toContain("__('common.public_git_repository')")
        ->toContain("__('common.choose_github_app')")
        ->toContain("__('common.choose_gitlab_app')")
        ->toContain("__('common.private_repository')")
        ->toContain("__('common.build_configuration')")
        ->toContain("__('common.choose_resource')")
        ->toContain("__('common.select_postgresql_image')")
        ->toContain("__('common.check_all_dns')")
        ->toContain("__('common.domain_settings')")
        ->toContain("__('common.dns_not_pointing_to_ip')")
        ->toContain("__('common.s3_storage_title')")
        ->toContain("__('common.backup_schedules')")
        ->toContain("__('common.confirm_storage_deletion')")
        ->toContain("__('common.empty_project')")
        ->toContain("__('common.proxy_configuration')")
        ->toContain("__('common.dynamic_configurations')")
        ->toContain("__('common.proxy_logs')")
        ->toContain("__('common.log_drains')")
        ->toContain("__('common.sentinel_out_of_sync')")
        ->toContain("__('common.view_logs')")
        ->toContain("__('common.metrics')")
        ->toContain("__('common.transfer_server')")
        ->toContain("__('common.import_server_transfer')")
        ->toContain("__('common.server_resources')")
        ->not->toContain('>Welcome to Coolify<')
        ->not->toContain('>Server is not reachable<')
        ->not->toContain('>No matching projects<')
        ->not->toContain('>No matching servers<');

    expect($content)
        ->not->toContain('>User lookup<')
        ->not->toContain('>Clone environment<')
        ->not->toContain('>Deployment history<')
        ->not->toContain('>Database backups<');

    expect($content)
        ->not->toContain('>Save changes<')
        ->not->toContain('>Go back<')
        ->not->toContain('>Delete Team<')
        ->not->toContain('>Refresh Proxy Status<');

    expect($content)
        ->not->toContain('>Empty Project<')
        ->not->toContain('>Proxy logs<')
        ->not->toContain('>Dynamic configurations<')
        ->not->toContain('>Server validation required<')
        ->not->toContain('>Restart Proxy<');

    expect($content)
        ->not->toContain('>Transfer server<')
        ->not->toContain('>Transferring…<')
        ->not->toContain('>Disable metrics<')
        ->not->toContain('>Metrics are disabled<');

    expect($content)
        ->not->toContain('>Import server transfer<')
        ->not->toContain('>Back to servers<')
        ->not->toContain('>Managed<')
        ->not->toContain('>Unmanaged<');

    expect(file_get_contents(app_path('Livewire/Boarding/Index.php')))
        ->toContain("__('onboarding.localhost_not_found')")
        ->toContain("__('onboarding.project_not_found')");
});

it('uses translation keys across database and service settings', function () {
    $views = [
        resource_path('views/livewire/project/database/status.blade.php'),
        resource_path('views/livewire/project/database/import.blade.php'),
        resource_path('views/livewire/project/database/heading.blade.php'),
        resource_path('views/livewire/project/database/backup-executions.blade.php'),
        resource_path('views/livewire/project/database/init-script.blade.php'),
        resource_path('views/livewire/project/database/backup-edit/s3.blade.php'),
        resource_path('views/livewire/project/database/backup-edit/retention.blade.php'),
        resource_path('views/livewire/project/database/backup-edit/danger.blade.php'),
        resource_path('views/livewire/project/database/postgresql/general.blade.php'),
        resource_path('views/livewire/project/database/mysql/general.blade.php'),
        resource_path('views/livewire/project/database/mariadb/general.blade.php'),
        resource_path('views/livewire/project/database/redis/general.blade.php'),
        resource_path('views/livewire/project/database/mongodb/general.blade.php'),
        resource_path('views/livewire/project/database/keydb/general.blade.php'),
        resource_path('views/livewire/project/database/dragonfly/general.blade.php'),
        resource_path('views/livewire/project/database/clickhouse/general.blade.php'),
        resource_path('views/livewire/project/service/index.blade.php'),
        resource_path('views/livewire/project/service/advanced-settings.blade.php'),
        resource_path('views/livewire/project/service/edit-compose.blade.php'),
        resource_path('views/livewire/project/service/import-backup.blade.php'),
        resource_path('views/livewire/project/service/file-storage.blade.php'),
        resource_path('views/livewire/project/service/resource-card.blade.php'),
        resource_path('views/livewire/project/service/stack-form.blade.php'),
        resource_path('views/livewire/project/service/status.blade.php'),
        resource_path('views/livewire/project/service/database-backups.blade.php'),
        resource_path('views/livewire/project/service/backup-executions.blade.php'),
        resource_path('views/livewire/project/service/volume-backup/index.blade.php'),
        resource_path('views/livewire/project/service/volume-backup/show.blade.php'),
    ];

    $content = implode("\n", array_map('file_get_contents', $views));

    expect($content)
        ->toContain("__('common.database_public_access_description')")
        ->toContain("__('common.keydb_image_helper')")
        ->toContain("__('common.clickhouse_image_helper')")
        ->toContain("__('common.keep_prefixes')")
        ->toContain("__('common.convert_to_file')")
        ->toContain("__('common.convert_to_directory')")
        ->toContain("__('common.backup_schedule', ['frequency'")
        ->toContain("__('common.executions')")
        ->not->toContain('Expose this database through the managed TCP proxy.')
        ->not->toContain('Storage Backups | Coolify')
        ->not->toContain('Back to backups')
        ->not->toContain('>Restart<')
        ->not->toContain('>Stop<');
});

it('keeps the English and Simplified Chinese catalogs aligned', function () {
    $englishSource = file_get_contents(lang_path('en.json'));
    $chineseSource = file_get_contents(lang_path('zh-cn.json'));
    $english = json_decode($englishSource, true, 512, JSON_THROW_ON_ERROR);
    $chinese = json_decode($chineseSource, true, 512, JSON_THROW_ON_ERROR);

    expect(array_keys($english))
        ->toEqualCanonicalizing(array_keys($chinese));

    expect($chineseSource)
        ->not->toMatch('/\\\\u[0-9a-fA-F]{4}/');
});
