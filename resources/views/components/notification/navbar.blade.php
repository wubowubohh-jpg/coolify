@props([
    'title' => __('common.notifications'),
    'subtitle' => __('common.notification_description'),
    // Topbar + channel tabs identify the page at xl+; keep the H1 on tablet.
    'titleOnDesktop' => false,
])

<x-dashboard.navbar section="notifications" :title="$title" :subtitle="$subtitle" :titleOnDesktop="$titleOnDesktop" />
