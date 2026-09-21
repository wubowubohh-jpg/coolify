<div class="flex flex-col items-center justify-center h-32">
    <span class="text-xl font-bold dark:text-white">{{ __('common.creation_limit_reached', ['name' => $name]) }}</span>
    @php($upgradeLink = '<a class="dark:text-white underline" '.wireNavigate().' href="'.route('subscription.show').'">'.e(__('common.upgrade_subscription')).'</a>')
    <span>{!! __('common.upgrade_subscription_to_create', ['upgrade_link' => $upgradeLink, 'name' => e($name)]) !!}</span>
</div>
