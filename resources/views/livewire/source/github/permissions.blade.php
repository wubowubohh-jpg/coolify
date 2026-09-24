            <div class="application-settings-form">
                <x-application.settings-section :title="__('common.permissions')"
                    :description="__('source.github_permissions_description')">
                    <x-slot:actions>
                        @can('view', $github_app)
                            <x-forms.button type="button" wire:click.prevent="checkPermissions">
                                <x-reicon name="refresh" class="size-3.5" />
                                {{ __('source.refetch') }}
                            </x-forms.button>
                            <a href="{{ getPermissionsPath($github_app) }}" class="button">
                                {{ __('source.update_on_github') }}
                                <x-external-link />
                            </a>
                        @endcan
                    </x-slot:actions>

                    <div class="grid gap-4 lg:grid-cols-3">
                        <x-forms.input canGate="view" :canResource="$github_app" id="contents"
                            :helper="__('source.read_access_mandatory')" :label="__('source.contents')" readonly placeholder="N/A" />
                        <x-forms.input canGate="view" :canResource="$github_app" id="metadata"
                            :helper="__('source.read_access_mandatory')" :label="__('source.metadata')" readonly placeholder="N/A" />
                        <x-forms.input canGate="view" :canResource="$github_app" id="pullRequests"
                            :helper="__('source.pull_requests_preview_helper')"
                            :label="__('source.pull_requests')" readonly placeholder="N/A" />
                    </div>
                </x-application.settings-section>
            </div>
