<div>
    <x-slot:title>
        {{ __('common.members') }} | Coolify
    </x-slot>

    <x-team.settings-layout>
    <div class="flex flex-col gap-6">
        <form wire:submit="submit" class="application-settings-form">
            <x-unsaved-bar action="submit" />
            <x-application.settings-section :title="__('common.general')"
                :description="__('common.team_identity_api_description')">
                <x-slot:actions>
                    <x-modal-input :buttonTitle="__('common.new_team')" :title="__('common.new_team')">
                        <livewire:team.create />
                    </x-modal-input>
                </x-slot:actions>
                <div class="grid gap-4 lg:grid-cols-2">
                    <x-forms.input id="name" :label="__('common.name')" required canGate="update" :canResource="$team" />
                    <x-forms.input id="description" :label="__('common.description')" canGate="update" :canResource="$team" />
                    <div class="lg:col-span-2">
                        <x-forms.listbox canGate="update" :canResource="$team" id="is_mcp_server_enabled" :label="__('common.mcp_server')"
                            :helper="__('common.mcp_server_team_helper')"
                            :disabled="! auth()->user()->can('update', $team)" :options="[
                                ['value' => false, 'label' => __('common.disabled_for_team')],
                                ['value' => true, 'label' => __('common.enabled_for_team')],
                            ]" />
                    </div>
                </div>
            </x-application.settings-section>
        </form>

    </div>
    </x-team.settings-layout>
</div>
