<form wire:submit.prevent="submit" class="application-settings-form flex flex-col gap-6">
    <x-unsaved-bar action="submit" />

    <x-application.settings-section :title="__('common.service_details')"
        :description="__('common.service_details_description')">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @if (isDev())
                    <x-status-badge :label="__('common.parser_version', ['version' => $service->compose_parsing_version])" type="neutral" />
                @endif
                @can('update', $service)
                    <x-modal-input :buttonTitle="__('common.edit_compose_file')" :title="__('common.docker_compose')" :closeOutside="false"
                        :isLarge="true">
                        <x-slot:footer>
                            <div x-data="{
                                preview: false,
                                validating: false,
                                saving: false,
                                sourceLabel: @js(__('common.back_to_source_compose')),
                                previewLabel: @js(__('common.preview_generated_compose')),
                            }"
                                @compose-validate-finished.window="validating = false"
                                @compose-save-finished.window="saving = false"
                                class="flex flex-wrap items-center justify-end gap-2">
                                <x-forms.button
                                    @click="preview = !preview; $dispatch('compose-preview-toggle')">
                                    <x-reicon name="eye" class="size-3.5" />
                                    <span x-text="preview ? sourceLabel : previewLabel"></span>
                                </x-forms.button>
                                @if (blank($service->service_type))
                                    <x-forms.button @click="validating = true; $dispatch('compose-validate')"
                                        x-bind:disabled="validating">
                                        <x-loading-on-button x-show="validating" x-cloak />
                                        {{ __('common.validate_compose') }}
                                    </x-forms.button>
                                @endif
                                <x-forms.button @click="saving = true; $dispatch('compose-save')"
                                    x-bind:disabled="saving" isHighlighted>
                                    <x-loading-on-button x-show="saving" x-cloak />
                                    {{ __('common.save_changes') }}
                                </x-forms.button>
                            </div>
                        </x-slot:footer>
                        <livewire:project.service.edit-compose serviceId="{{ $service->id }}" />
                    </x-modal-input>
                @endcan
                <x-modal-input :title="__('common.resource_details_title')" :buttonTitle="__('common.details')">
                    <livewire:project.shared.resource-details :resource="$service" />
                </x-modal-input>
            </div>
        </x-slot:actions>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-forms.input canGate="update" :canResource="$service" id="name" required :label="__('common.service_name')"
                placeholder="My WordPress site" />
            <x-forms.input canGate="update" :canResource="$service" id="description" :label="__('common.description')" />
        </div>
    </x-application.settings-section>

    <x-application.settings-section :title="__('common.network')"
        description="Control whether this Compose stack joins Coolify's predefined network.">
        <x-forms.listbox canGate="update" :canResource="$service" id="connectToDockerNetwork" :label="__('common.network_attachment')" live onChange="instantSave"
            :disabled="! auth()->user()->can('update', $service)" :options="[
                ['value' => false, 'label' => __('common.use_stack_network_only')],
                ['value' => true, 'label' => __('common.connect_predefined_coolify_network')],
            ]" />
    </x-application.settings-section>

    @if ($fields->count() > 0)
        <x-application.settings-section :title="__('common.service_configuration')"
            :description="__('common.service_configuration_description')">
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($fields as $serviceName => $field)
                    <div>
                        <div class="mb-1.5 flex items-center gap-1.5 text-[12px] font-medium">
                            <span>
                                @if (filled(data_get($field, 'serviceName')))
                                    {{ data_get($field, 'serviceName') }} ·
                                @endif
                                {{ data_get($field, 'name') }}
                            </span>
                            @if (data_get($field, 'customHelper'))
                                <x-helper helper="{{ data_get($field, 'customHelper') }}" />
                            @else
                                <x-helper :helper="__('common.template_variable_name', ['name' => $serviceName])" />
                            @endif
                        </div>
                        @if ($isPasswordHiddenForMember && data_get($field, 'isPassword'))
                            <x-forms.input disabled :value="__('common.hidden_admins_only')" />
                        @else
                            <x-forms.input canGate="update" :canResource="$service"
                                type="{{ data_get($field, 'isPassword') ? 'password' : 'text' }}"
                                required="{{ str(data_get($field, 'rules'))?->contains('required') }}"
                                id="fields.{{ $serviceName }}.value" />
                        @endif
                    </div>
                @endforeach
            </div>
        </x-application.settings-section>
    @endif
</form>
