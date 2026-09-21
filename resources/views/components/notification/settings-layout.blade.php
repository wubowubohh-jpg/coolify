@php
    $notificationMenuItems = [
        ['label' => 'Email', 'route' => 'notifications.email', 'icon' => 'mail'],
        ['label' => 'Discord', 'route' => 'notifications.discord', 'brandIcon' => 'discord'],
        ['label' => 'Telegram', 'route' => 'notifications.telegram', 'brandIcon' => 'telegram'],
        ['label' => 'Slack', 'route' => 'notifications.slack', 'brandIcon' => 'slack'],
        ['label' => 'Pushover', 'route' => 'notifications.pushover', 'brandIcon' => 'pushover'],
        ['label' => 'Webhook', 'route' => 'notifications.webhook', 'icon' => 'destinations'],
    ];
    $notificationTranslations = [
        'Email' => 'settings.email',
        'Discord' => 'common.discord',
        'Telegram' => 'common.telegram',
        'Slack' => 'common.slack',
        'Pushover' => 'common.pushover',
        'Webhook' => 'common.webhook',
    ];
@endphp

<section class="application-settings-workspace w-full max-w-none">
    <header class="settings-mobile-header xl:hidden">
        <h1 class="settings-mobile-title">{{ __('common.notifications') }}</h1>
        <p class="settings-mobile-description">{{ __('common.notification_description') }}</p>
    </header>
    <div class="grid min-w-0 gap-8 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <aside class="application-settings-navigation min-w-0 xl:self-start">
            <nav aria-label="{{ __('common.notification_settings') }}"
                class="grid grid-cols-2 gap-0.5 border-y border-neutral-200 py-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-1 xl:border-y-0 xl:py-0 dark:border-white/[0.06]">
                <div class="nav-section hidden xl:block">{{ __('common.notifications') }}</div>
                @foreach ($notificationMenuItems as $menuItem)
                    <a wire:key="notification-settings-{{ str($menuItem['label'])->slug() }}"
                        @class(['menu-item', 'menu-item-active' => request()->routeIs($menuItem['route'])])
                        {{ wireNavigate() }} href="{{ route($menuItem['route']) }}">
                        @if (isset($menuItem['brandIcon']))
                            <span class="menu-item-icon bg-current"
                                style="mask: url('{{ asset('svgs/' . $menuItem['brandIcon'] . '.svg') }}') center / contain no-repeat; -webkit-mask: url('{{ asset('svgs/' . $menuItem['brandIcon'] . '.svg') }}') center / contain no-repeat;"></span>
                        @else
                            <x-reicon :name="$menuItem['icon']" class="menu-item-icon" />
                        @endif
                        <span class="menu-item-label">{{ __($notificationTranslations[$menuItem['label']] ?? $menuItem['label']) }}</span>
                    </a>
                @endforeach
            </nav>
        </aside>

        <div class="min-w-0">
            {{ $slot }}
        </div>
    </div>
</section>
