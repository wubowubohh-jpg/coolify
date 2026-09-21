<div>
    <x-slot:title>
        {{ __('common.import_server_transfer') }} | Coolify
    </x-slot>
    <div class="flex flex-col gap-6">
        <div class="flex flex-wrap items-center gap-2">
            <h1>{{ __('common.import_server_transfer') }} <x-status-badge :label="__('common.development')" /></h1>
            <a href="{{ route('server.index') }}" {{ wireNavigate() }}>
                <x-forms.button>{{ __('common.back_to_servers') }}</x-forms.button>
            </a>
        </div>
        <div class="subtitle">
            {!! __('common.import_server_transfer_description', ['claim' => '<strong>'.__('common.claims').'</strong>']) !!}
        </div>

        <div class="flex flex-col gap-4 rounded-lg border border-neutral-200 p-4 dark:border-coolgray-200">
            <x-forms.input type="file" id="bundleFile" :label="__('common.bundle_file_json')" accept=".json,application/json" />
            <x-forms.textarea id="bundleJson" :label="__('common.or_paste_bundle_json')" rows="14" placeholder='{"schema_version":1,...}' />
            <x-forms.input id="passphrase" type="password" :label="__('common.passphrase_if_encrypted')" :placeholder="__('common.optional')" />
            <div class="flex flex-col gap-2">
                <x-forms.checkbox id="preserveUuids" :label="__('common.preserve_source_uuids')" />
                <x-forms.checkbox id="adoptMode" :label="__('common.adopt_mode')" />
                <x-forms.checkbox id="writeRemote" :label="__('common.write_ownership_file_optional')" />
            </div>
            <div class="flex flex-wrap gap-2">
                <x-forms.button wire:click="dryRun" wire:loading.attr="disabled" wire:target="dryRun,importBundle">
                    <span wire:loading.remove wire:target="dryRun">{{ __('common.dry_run') }}</span>
                    <span wire:loading wire:target="dryRun">{{ __('common.checking') }}</span>
                </x-forms.button>
                <x-forms.button wire:click="importBundle" wire:loading.attr="disabled"
                    wire:target="dryRun,importBundle"
                    wire:confirm="{{ __('common.import_server_confirmation') }}">
                    <span wire:loading.remove wire:target="importBundle">{{ __('common.import_server') }}</span>
                    <span wire:loading wire:target="importBundle">{{ __('common.importing') }}</span>
                </x-forms.button>
            </div>
        </div>

        @if (count($lastWarnings) > 0)
            <div class="rounded-lg border border-warning/40 bg-warning/10 p-3 text-sm">
                <div class="mb-1 font-semibold text-warning">{{ __('common.warnings') }}</div>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($lastWarnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($lastResult)
            <div class="rounded-lg border border-neutral-200 p-4 dark:border-coolgray-200">
                <div class="mb-2 font-semibold">
                    {{ data_get($lastResult, 'dry_run') ? __('common.dry_run_result') : __('common.import_result') }}
                </div>
                @if ($importedServerUuid)
                    <div class="mb-3 flex flex-wrap items-center gap-2 text-sm">
                        <span>{{ __('common.server_uuid') }}: <code class="font-mono">{{ $importedServerUuid }}</code></span>
                        @if (data_get($lastResult, 'claimed'))
                            <span class="text-success">{{ __('common.claimed') }}</span>
                        @endif
                        <a href="{{ route('server.show', ['server_uuid' => $importedServerUuid]) }}" {{ wireNavigate() }}>
                            <x-forms.button>{{ __('common.open_server') }}</x-forms.button>
                        </a>
                        <a href="{{ route('server.transfer', ['server_uuid' => $importedServerUuid]) }}" {{ wireNavigate() }}>
                            <x-forms.button>{{ __('common.transfer_details') }}</x-forms.button>
                        </a>
                    </div>
                @endif
                <pre class="max-h-80 overflow-auto rounded-lg bg-neutral-100 p-3 text-xs dark:bg-coolgray-100">{{ json_encode($lastResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif
    </div>
</div>
