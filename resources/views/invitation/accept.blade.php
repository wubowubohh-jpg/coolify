<x-layout-simple>
    <x-auth.shell title="Coolify" :description="__('common.invitation_description')">
        <div class="flex flex-col gap-4">
            <div class="auth-guidance">
                <x-reicon name="teams" class="mt-0.5 size-4 shrink-0" />
                <p>{{ __('common.invited_to_collaborate') }}</p>
            </div>

            <dl class="divide-y divide-neutral-200 rounded-lg border border-neutral-200 text-sm dark:divide-white/10 dark:border-white/10">
                <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                    <dt class="text-neutral-500 dark:text-fg-dim">{{ __('common.team_label') }}</dt>
                    <dd class="min-w-0 truncate font-medium text-neutral-900 dark:text-white">{{ $team->name }}</dd>
                </div>
                <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                    <dt class="text-neutral-500 dark:text-fg-dim">{{ __('common.role') }}</dt>
                    <dd class="font-medium text-neutral-900 dark:text-white">{{ ucfirst($invitation->role) }}</dd>
                </div>
            </dl>

            @if ($alreadyMember)
                <x-auth.alert type="warning">{{ __('common.already_member_invitation') }}</x-auth.alert>
            @endif

            <form method="POST" action="{{ $formAction }}">
                @csrf
                @isset($token)
                    <input type="hidden" name="token" value="{{ $token }}">
                @endisset
                <x-forms.button class="w-full justify-center" type="submit" isHighlighted>
                    {{ $alreadyMember ? __('common.dismiss_invitation') : __('common.accept_invitation') }}
                </x-forms.button>
            </form>
        </div>
    </x-auth.shell>
</x-layout-simple>
