<form wire:submit="submit" class="application-settings-form flex flex-col gap-6">
    <x-unsaved-bar action="submit" />

    <x-application.settings-section :title="__('common.healthcheck')"
        :description="__('common.healthcheck_description')">
        <x-slot:actions>
            @if (!$healthCheckEnabled)
                <x-modal-confirmation :title="__('common.enable_healthcheck').'?'" :buttonTitle="__('common.enable_healthcheck')"
                    submitAction="toggleHealthcheck" :actions="[__('common.enable_healthcheck_for_database')]"
                    :warningMessage="__('common.healthcheck_warning')"
                    :step2ButtonText="__('common.enable_healthcheck')" :confirmWithText="false" :confirmWithPassword="false"
                    isHighlightedButton />
            @else
                <x-forms.button canGate="update" :canResource="$database"
                    wire:click="toggleHealthcheck" type="button">{{ __('common.disable_healthcheck') }}</x-forms.button>
            @endif
        </x-slot:actions>

        @if (!$healthCheckEnabled)
            <x-callout type="warning" :title="__('common.healthcheck_disabled')">
                {{ __('common.healthcheck_disabled_description') }}
            </x-callout>
        @endif

        <div class="{{ !$healthCheckEnabled ? 'mt-4 ' : '' }}grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-forms.input canGate="update" :canResource="$database" min="1" type="number"
                id="healthCheckInterval" placeholder="15" :label="__('common.interval_seconds')" required />
            <x-forms.input canGate="update" :canResource="$database" min="1" type="number"
                id="healthCheckTimeout" placeholder="5" :label="__('common.timeout_seconds')" required />
            <x-forms.input canGate="update" :canResource="$database" min="1" type="number"
                id="healthCheckRetries" placeholder="5" :label="__('common.retries')" required />
            <x-forms.input canGate="update" :canResource="$database" min="0" type="number"
                id="healthCheckStartPeriod" placeholder="5" :label="__('common.start_period_seconds')" required />
        </div>
    </x-application.settings-section>
</form>
