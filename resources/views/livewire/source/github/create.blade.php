@can('createAnyResource')
    <form wire:submit="createGitHubApp" class="flex w-full flex-col gap-4">
        <p class="text-[12px] leading-5 text-neutral-500 dark:text-fg-dim">
            {{ __('source.connect_github_app_description') }}
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-forms.input id="name" :label="__('common.name')" required />
            <x-forms.input id="organization" :label="__('source.organization')"
                :helper="__('source.personal_account_when_empty')"
                :placeholder="__('source.personal_account_when_empty')" />
        </div>

        @if (! isCloud())
            <div x-data="{ showWarning: @entangle('is_system_wide') }">
                <div class="max-w-xs">
                    <x-forms.checkbox id="is_system_wide" :label="__('source.system_wide')"
                        :helper="__('source.system_wide_github_helper')" />
                </div>
                <div x-cloak x-show="showWarning" x-transition class="mt-3">
                    <x-callout type="warning" :title="__('source.shared_with_every_team')">
                        {{ __('source.team_specific_isolation') }}
                    </x-callout>
                </div>
            </div>
        @endif

        <div x-data="{
            open: false,
        }" class="rounded-lg border border-neutral-200 dark:border-white/[0.08]">
            <button type="button" @click="open = !open"
                class="flex w-full items-center justify-between px-3 py-2.5 text-left text-[12px] font-medium text-neutral-700 transition-colors hover:bg-neutral-50 dark:text-fg-dim dark:hover:bg-white/[0.03]">
                {{ __('source.self_hosted_enterprise_github') }}
                <svg class="size-3.5 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div x-cloak x-show="open" x-collapse.duration.200ms class="border-t border-neutral-200 px-3 py-3 dark:border-white/[0.08]">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-forms.input id="html_url" :label="__('source.html_url')" required
                        :helper="__('source.github_enterprise_url_helper')" />
                    <x-forms.input id="api_url" :label="__('source.api_url')" required
                        :helper="__('source.github_api_url_helper')" />
                    <x-forms.input id="custom_user" :label="__('source.custom_git_user')" required />
                    <x-forms.input id="custom_port" type="number" :label="__('source.custom_git_port')" required />
                </div>
            </div>
        </div>

        <x-forms.button class="mt-1 w-full justify-center" type="submit">
            {{ __('source.continue') }}
        </x-forms.button>
    </form>
@else
    <x-callout type="danger" :title="__('common.insufficient_permissions')">
        {{ __('source.create_github_apps_permission') }}
    </x-callout>
@endcan
