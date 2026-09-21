<div>
    <x-slot:title>
        Notifications | Coolify
    </x-slot>

    <x-notification.settings-layout>
    <div class="flex flex-col gap-6">
        <form wire:submit="submit" class="application-settings-form">
            <x-unsaved-bar action="submit" />
            <x-application.settings-section :title="__('common.email_delivery')">
                <x-slot:actions>
                    @if (auth()->user()->isAdminFromSession())
                        @can('sendTest', $settings)
                            @if ($team->isNotificationEnabled('email'))
                                <x-modal-input :title="__('common.send_test_email')">
                                    <x-slot:content>
                                        <button type="button" class="button">
                                            <x-reicon name="notifications" class="size-3.5" />
                                            {{ __('common.send_test') }}
                                        </button>
                                    </x-slot:content>
                                    <form wire:submit.prevent="sendTestEmail" class="flex w-full flex-col gap-4">
                                        <x-forms.input wire:model="testEmailAddress" placeholder="test@example.com"
                                            id="testEmailAddress" :label="__('common.recipient')" required />
                                        <div class="flex justify-end border-t border-neutral-200 pt-4 dark:border-white/[0.08]">
                                            <button type="submit" @click="modalOpen=false"
                                                class="button button-highlighted">
                                                {{ __('common.send_email') }}
                                            </button>
                                        </div>
                                    </form>
                                </x-modal-input>
                            @else
                                <button type="button" class="button" disabled>{{ __('common.send_test') }}</button>
                            @endif
                        @endcan
                    @endif
                </x-slot:actions>

                <div class="grid gap-4 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        @if (isCloud())
                            <div class="w-full sm:w-72">
                                <x-forms.listbox canGate="update" :canResource="$settings" id="useInstanceEmailSettings" :label="__('common.email_service')"
                                    onChange="instantSave"
                                    :disabled="!auth()->user()->can('update', $settings)" :options="[
                                        ['value' => true, 'label' => __('common.use_hosted_email_service')],
                                        ['value' => false, 'label' => __('common.use_team_email_settings')],
                                    ]" />
                            </div>
                        @else
                            <div class="w-full sm:w-72">
                                <x-forms.listbox canGate="update" :canResource="$settings" id="useInstanceEmailSettings" :label="__('common.email_service')"
                                    onChange="instantSave"
                                    :disabled="!auth()->user()->can('update', $settings)" :options="[
                                        ['value' => true, 'label' => __('common.use_system_wide_settings')],
                                        ['value' => false, 'label' => __('common.use_team_email_settings')],
                                    ]" />
                            </div>
                        @endif
                    </div>

                    @if (!$useInstanceEmailSettings)
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpFromName"
                            :helper="__('common.name_used_in_emails')" :label="__('common.from_name')" />
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpFromAddress"
                            :helper="__('common.email_address_used_in_emails')" :label="__('common.from_address')" />

                        @if (isInstanceAdmin())
                            <div class="lg:col-span-2">
                                <x-forms.button type="button" wire:click="copyFromInstanceSettings">
                                    {{ __('common.copy_from_instance_settings') }}
                                </x-forms.button>
                            </div>
                        @endif
                    @endif
                </div>
            </x-application.settings-section>
        </form>

        @if (!$useInstanceEmailSettings)
            <div class="application-settings-form">
                <x-application.settings-section :title="__('common.smtp_server')"
                    :description="__('common.smtp_description')">
                    <div class="grid gap-4 lg:grid-cols-3">
                        <div class="lg:col-span-3">
                            <div class="w-full sm:w-72">
                                <x-forms.listbox canGate="update" :canResource="$settings" id="smtpEnabled" :label="__('common.smtp_delivery')"
                                    onChange="submitSmtp"
                                    :disabled="!auth()->user()->can('update', $settings)" :options="[
                                        ['value' => true, 'label' => 'Enabled'],
                                        ['value' => false, 'label' => 'Disabled'],
                                    ]" />
                            </div>
                        </div>
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpHost"
                            placeholder="smtp.mailgun.org" label="Host" />
                        <x-forms.input canGate="update" :canResource="$settings" required id="smtpPort"
                            type="number" placeholder="587" label="Port" />
                        <x-forms.listbox canGate="update" :canResource="$settings" id="smtpEncryption" :label="__('common.encryption')" required
                            :disabled="!auth()->user()->can('update', $settings)" :options="[
                            ['value' => 'starttls', 'label' => 'StartTLS'],
                            ['value' => 'tls', 'label' => 'TLS / SSL'],
                            ['value' => 'none', 'label' => __('common.none')],
                        ]" />
                        <x-forms.input canGate="update" :canResource="$settings" id="smtpUsername"
                            :label="__('common.smtp_username')" />
                        @can('update', $settings)
                            <x-forms.input canGate="update" :canResource="$settings" id="smtpPassword" type="password"
                                :label="__('common.smtp_password')" />
                        @else
                            <x-forms.input disabled :label="__('common.smtp_password')" :value="__('common.hidden_admins_only')" />
                        @endcan
                        <x-forms.input canGate="update" :canResource="$settings" id="smtpTimeout" type="number"
                            :helper="__('common.timeout_sending_emails')" :label="__('common.timeout')" />
                        <x-forms.input canGate="update" :canResource="$settings" id="smtpEhloDomain"
                            placeholder="coolify.example.com"
                            :helper="__('common.ehlo_domain_description')"
                            :label="__('common.ehlo_domain')" />
                    </div>
                </x-application.settings-section>
            </div>

            <div class="application-settings-form">
                <x-application.settings-section :title="__('common.resend')">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <x-forms.listbox canGate="update" :canResource="$settings" id="resendEnabled" :label="__('common.resend_delivery')"
                            onChange="submitResend"
                            :disabled="!auth()->user()->can('update', $settings)" :options="[
                                ['value' => true, 'label' => 'Enabled'],
                                ['value' => false, 'label' => 'Disabled'],
                            ]" />
                        @can('update', $settings)
                            <x-forms.input canGate="update" :canResource="$settings" :required="$resendEnabled"
                                type="password" id="resendApiKey" :placeholder="__('common.api_key')" :label="__('common.api_key')"
                                autocomplete="new-password" />
                        @else
                            <x-forms.input disabled :label="__('common.api_key')" :value="__('common.hidden_admins_only')" />
                        @endcan
                    </div>
                </x-application.settings-section>
            </div>
        @endif

        <div class="application-settings-form">
            <x-application.settings-section :title="__('common.notification_events')">
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-notification.event-multiselect :settings="$settings" id="deployment-email-events" :label="__('common.deployments')"
                        :events="[
                            ['property' => 'deploymentSuccessEmailNotifications', 'label' => __('common.deployment_success'), 'enabled' => $deploymentSuccessEmailNotifications],
                            ['property' => 'deploymentFailureEmailNotifications', 'label' => __('common.deployment_failure'), 'enabled' => $deploymentFailureEmailNotifications],
                        ]" />
                    <x-notification.event-multiselect :settings="$settings" id="resource-email-events" :label="__('common.resources')"
                        :events="[
                            ['property' => 'statusChangeEmailNotifications', 'label' => __('common.resource_status_changes'), 'enabled' => $statusChangeEmailNotifications],
                            ['property' => 'restartLimitReachedEmailNotifications', 'label' => __('common.restart_limit_reached_event'), 'enabled' => $restartLimitReachedEmailNotifications],
                        ]" />
                    <x-notification.event-multiselect :settings="$settings" id="backup-email-events" :label="__('common.backups')"
                        :events="[
                            ['property' => 'backupSuccessEmailNotifications', 'label' => __('common.backup_success'), 'enabled' => $backupSuccessEmailNotifications],
                            ['property' => 'backupFailureEmailNotifications', 'label' => __('common.backup_failure'), 'enabled' => $backupFailureEmailNotifications],
                        ]" />
                    <x-notification.event-multiselect :settings="$settings" id="scheduled-task-email-events"
                        :label="__('common.scheduled_tasks')" :events="[
                            ['property' => 'scheduledTaskSuccessEmailNotifications', 'label' => __('common.scheduled_task_success'), 'enabled' => $scheduledTaskSuccessEmailNotifications],
                            ['property' => 'scheduledTaskFailureEmailNotifications', 'label' => __('common.scheduled_task_failure'), 'enabled' => $scheduledTaskFailureEmailNotifications],
                        ]" />
                    <x-notification.event-multiselect :settings="$settings" id="server-email-events" :label="__('common.server_event_label')"
                        :events="[
                            ['property' => 'dockerCleanupSuccessEmailNotifications', 'label' => __('common.docker_cleanup_success'), 'enabled' => $dockerCleanupSuccessEmailNotifications],
                            ['property' => 'dockerCleanupFailureEmailNotifications', 'label' => __('common.docker_cleanup_failure'), 'enabled' => $dockerCleanupFailureEmailNotifications],
                            ['property' => 'serverDiskUsageEmailNotifications', 'label' => __('common.server_disk_usage'), 'enabled' => $serverDiskUsageEmailNotifications],
                            ['property' => 'serverReachableEmailNotifications', 'label' => __('common.server_reachable'), 'enabled' => $serverReachableEmailNotifications],
                            ['property' => 'serverUnreachableEmailNotifications', 'label' => __('common.server_unreachable'), 'enabled' => $serverUnreachableEmailNotifications],
                            ['property' => 'serverPatchEmailNotifications', 'label' => __('common.server_patching'), 'enabled' => $serverPatchEmailNotifications],
                            ['property' => 'traefikOutdatedEmailNotifications', 'label' => __('common.traefik_proxy_outdated'), 'enabled' => $traefikOutdatedEmailNotifications],
                        ]" />
                </div>
            </x-application.settings-section>
        </div>
    </div>
    </x-notification.settings-layout>
</div>
