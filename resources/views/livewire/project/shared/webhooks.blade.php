<div>
    @if ($resource->type() === 'application')
        @php
            $manualWebhookProviders = [
                [
                    'name' => 'GitHub',
                    'url' => $githubManualWebhook,
                    'secret' => 'githubManualWebhookSecret',
                    'description' => __('source.github_webhook_description'),
                ],
                [
                    'name' => 'GitLab',
                    'url' => $gitlabManualWebhook,
                    'secret' => 'gitlabManualWebhookSecret',
                    'description' => __('source.gitlab_webhook_description_manual'),
                ],
                [
                    'name' => 'Bitbucket',
                    'url' => $bitbucketManualWebhook,
                    'secret' => 'bitbucketManualWebhookSecret',
                    'description' => __('source.bitbucket_webhook_description'),
                ],
                [
                    'name' => 'Gitea',
                    'url' => $giteaManualWebhook,
                    'secret' => 'giteaManualWebhookSecret',
                    'description' => __('source.gitea_webhook_description'),
                ],
            ];
        @endphp

        <div class="flex flex-col gap-6">
            <x-application.settings-section id="deploy-webhook-section" :title="__('source.deploy_webhook')"
                :helper="__('source.deploy_webhook_helper')">
                <x-slot:actions>
                    <a class="button" href="https://coolify.io/docs/api-reference/authorization" target="_blank"
                        rel="noopener noreferrer">
                        {{ __('source.documentation') }}
                        <x-external-link />
                    </a>
                </x-slot:actions>
                <x-forms.copy-button :label="__('source.deploy_webhook_url')" :text="$deploywebhook ?? ''" />
            </x-application.settings-section>

            @if ($githubManualWebhook && $gitlabManualWebhook)
                <form wire:submit.prevent="submit" class="application-settings-form flex flex-col">
                    <x-unsaved-bar action="submit" />
                    <x-application.settings-section id="manual-git-webhooks-section" :title="__('source.manual_git_webhooks')"
                        :helper="__('source.manual_git_webhooks_helper')" flush>
                        <x-slot:actions>
                            @if (filled($resource?->gitWebhook))
                                <a class="button" href="{{ $resource->gitWebhook }}" target="_blank"
                                    rel="noopener noreferrer">
                                    {{ __('source.repository_settings') }}
                                    <x-external-link />
                                </a>
                            @endif
                        </x-slot:actions>

                        <div class="divide-y divide-neutral-200 dark:divide-white/[0.07]">
                            @foreach ($manualWebhookProviders as $provider)
                                <section class="px-4 py-5 first:pt-4 last:pb-4"
                                    wire:key="manual-webhook-{{ str($provider['name'])->slug() }}">
                                    <div class="mb-4">
                                        <h4 class="text-sm font-semibold text-black dark:text-fg">
                                            {{ $provider['name'] }}
                                        </h4>
                                        <p class="mt-1 text-[13px] leading-5 text-neutral-500 dark:text-fg-dim">
                                            {{ $provider['description'] }}
                                        </p>
                                    </div>
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <x-forms.copy-button :label="__('source.webhook_url')" :text="$provider['url'] ?? ''" />
                                        @can('update', $resource)
                                            <x-forms.input type="password" :id="$provider['secret']"
                                                :label="__('source.webhook_secret')"
                                                :helper="__('source.webhook_secret_helper', ['provider' => $provider['name']])"
                                                autocomplete="new-password" />
                                        @else
                                            <x-forms.input disabled :label="__('source.webhook_secret')"
                                                :value="__('source.webhook_secret_hidden')" />
                                        @endcan
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </x-application.settings-section>
                </form>
            @else
                <x-application.settings-section id="manual-git-webhooks-section" :title="__('source.manual_git_webhooks')"
                    :helper="__('source.manual_git_webhooks_empty_helper')">
                    <x-empty size="sm" :title="__('source.managed_by_git_app')"
                        :description="__('source.managed_by_git_app_description')"
                        icon-name="notifications" />
                </x-application.settings-section>
            @endif
        </div>
    @else
        <div class="application-settings-form">
            <x-application.settings-section :title="__('source.deploy_webhook')"
                :helper="__('source.deploy_webhook_external_helper')">
                <x-slot:actions>
                    <a class="button" href="https://coolify.io/docs/api-reference/authorization" target="_blank"
                        rel="noopener noreferrer">
                        {{ __('source.documentation') }}
                        <x-external-link />
                    </a>
                </x-slot:actions>
                <x-forms.copy-button :label="__('source.deploy_webhook_url')" :text="$deploywebhook ?? ''" />
            </x-application.settings-section>
        </div>
    @endif
</div>
