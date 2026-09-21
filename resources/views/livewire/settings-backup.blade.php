<div>
    <x-slot:title>
        {{ __('settings.backup') }} | Coolify
    </x-slot>

    <x-settings.layout>
    <div class="application-settings-form mx-auto flex w-full max-w-none min-w-0 flex-col gap-6">
        @if ($server->isFunctional())
            @if (isset($database) && isset($backup))
                <form wire:submit="submit">
                    <x-unsaved-bar action="submit" />

                    <x-application.settings-section :title="__('settings.instance_database')">
                        <div class="grid gap-4 lg:grid-cols-2">
                            <x-forms.input :label="__('settings.name')" readonly id="name" />
                            <x-forms.input :label="__('settings.description')" id="description" />
                            <div class="lg:col-span-2">
                                <x-forms.input :label="__('settings.uuid')" readonly id="uuid" />
                            </div>
                            <x-forms.input :label="__('settings.user')" readonly id="postgres_user" />
                            <x-forms.input type="password" :label="__('settings.password')" readonly id="postgres_password" />
                        </div>
                    </x-application.settings-section>
                </form>

                <livewire:project.database.backup-edit :backup="$backup" :available-s3-storages="$s3s"
                    :status="data_get($database, 'status')" />

                <livewire:project.database.backup-executions :backup="$backup" />
            @else
                <x-application.settings-section :title="__('settings.instance_backup')">
                    <x-empty :title="__('settings.backup_not_configured')"
                        :description="__('settings.backup_requires_database')"
                        icon-name="database" size="sm">
                        <x-slot:actions>
                            <x-forms.button wire:click="addCoolifyDatabase" isHighlighted>
                                {{ __('settings.configure_backup') }}
                            </x-forms.button>
                        </x-slot:actions>
                    </x-empty>
                </x-application.settings-section>
            @endif
        @else
            <x-application.settings-section :title="__('settings.instance_backup')">
                <x-callout type="danger" :title="__('settings.localhost_not_ready')">
                    {{ __('settings.validate_localhost') }}
                    <a href="{{ route('server.show', [$server->uuid]) }}" class="font-medium underline"
                        {{ wireNavigate() }}>{{ __('settings.open_server_settings') }}</a>
                </x-callout>
            </x-application.settings-section>
        @endif
    </div>
    </x-settings.layout>
</div>
