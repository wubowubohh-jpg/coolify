<div>
    @php
        $deletionBlockers = currentTeam()->deletionBlockers();
        $blockerDetails = [
            'projects' => ['label' => 'common.project_count', 'route' => 'project.index'],
            'servers' => ['label' => 'common.server_count', 'route' => 'server.index'],
            'sources' => ['label' => 'common.git_source_count', 'route' => 'source.all'],
        ];
    @endphp
    <x-slot:title>
        {{ __('common.danger_zone') }} | Coolify
    </x-slot>

    <x-team.settings-layout>
        <div class="application-settings-form">
            <x-application.settings-section id="team-danger-zone" :title="__('common.danger_zone')"
                :helper="__('common.destructive_actions_team')">
                <x-danger-zone :title="__('common.delete_team')">
                            @if (auth()->user()->roleInTeam(currentTeam()->id) !== 'owner')
                                <p>
                                    {{ __('common.only_team_owners_delete') }}
                                </p>
                            @elseif (session('currentTeam.id') === 0)
                                <p>
                                    {{ __('common.default_team_cannot_delete') }}
                                </p>
                            @elseif(auth()->user()->teams()->count() === 1 || auth()->user()->currentTeam()->personal_team)
                                <p>
                                    {{ __('common.last_personal_team_cannot_delete') }}
                                </p>
                            @elseif(currentTeam()->subscription)
                                <p>
                                    {!! __('common.cancel_subscription_before_delete', ['subscription' => '<a class="font-medium text-coollabs hover:underline dark:text-warning" '.wireNavigate().' href="'.route('subscription.show').'">'.e(__('common.subscription')).'</a>']) !!}
                                </p>
                            @elseif($deletionBlockers === [])
                                <p>
                                    {!! __('common.permanently_delete_team_description', ['name' => '<strong class="font-semibold text-black dark:text-fg">'.e(currentTeam()->name).'</strong>']) !!}
                                </p>
                                <ul class="space-y-1 text-xs">
                                    <li>{{ __('common.all_members_lose_access') }}</li>
                                    <li>{{ __('common.team_cannot_restore') }}</li>
                                </ul>
                            @else
                                <p>
                                    {{ __('common.team_still_owns') }}
                                </p>
                                <ul class="space-y-1">
                                    @foreach ($deletionBlockers as $type => $count)
                                        <li>
                                            <a class="font-medium text-coollabs hover:underline dark:text-warning"
                                                {{ wireNavigate() }} href="{{ route($blockerDetails[$type]['route']) }}">
                                                {{ trans_choice($blockerDetails[$type]['label'], $count, ['count' => $count]) }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <p>
                                    {{ __('common.remove_move_resources_before_delete') }}
                                </p>
                            @endif
                        <x-slot:action>
                            @if (
                                session('currentTeam.id') !== 0 &&
                                    auth()->user()->roleInTeam(currentTeam()->id) === 'owner' &&
                                    auth()->user()->teams()->count() > 1 &&
                                    !auth()->user()->currentTeam()->personal_team &&
                                    !currentTeam()->subscription &&
                                    $deletionBlockers === [])
                                <x-modal-confirmation :title="__('common.confirm_team_deletion')" :buttonTitle="__('common.delete_team')"
                                    isErrorButton submitAction="delete"
                                    :actions="[__('common.team_permanently_deleted_database')]"
                                    confirmationText="{{ currentTeam()->name }}"
                                    :confirmationLabel="__('common.enter_team_name_confirm')"
                                    :shortConfirmationLabel="__('common.team_name')" :confirmWithPassword="false"
                                    :step2ButtonText="__('common.permanently_delete_button')" canGate="delete"
                                    :canResource="$team" />
                            @else
                                <x-forms.button isError disabled :tooltip="__('common.resolve_team_delete_requirements')">
                                    {{ __('common.delete_team') }}
                                </x-forms.button>
                            @endif
                        </x-slot:action>
                </x-danger-zone>

                @if (session('currentTeam.id') !== 0 && !currentTeam()->subscription && (currentTeam()->projects->isNotEmpty() || currentTeam()->servers->isNotEmpty()))
                    <div class="mt-4 overflow-hidden rounded-lg border border-neutral-200 dark:border-white/[0.08]">
                        <div class="flex items-center justify-between gap-3 border-b border-neutral-200 px-3 py-2 dark:border-white/[0.08]">
                            <h5 class="text-sm font-medium text-black dark:text-fg">{{ __('common.resources') }}</h5>
                            <x-forms.button type="button" wire:click="refreshResources">
                                <x-reicon name="refresh" class="size-3.5" />
                                {{ __('common.refresh_resources') }}
                            </x-forms.button>
                        </div>
                        <table class="w-full text-left text-sm">
                            <thead class="bg-neutral-50 text-[11px] uppercase tracking-wide text-neutral-500 dark:bg-coolgray-100 dark:text-fg-dim">
                                <tr>
                                    <th class="px-3 py-2 font-medium">{{ __('common.resource_label') }}</th>
                                    <th class="px-3 py-2 font-medium">{{ __('common.name') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-200 dark:divide-white/[0.08]">
                                @foreach (currentTeam()->projects as $project)
                                    <tr class="text-[13px] text-neutral-600 hover:bg-neutral-50 dark:text-fg-dim dark:hover:bg-white/[0.03]">
                                        <td>
                                            <a class="block px-3 py-2.5" href="{{ route('project.show', ['project_uuid' => $project->uuid]) }}"
                                                target="_blank" rel="noopener noreferrer">{{ __('common.project') }}</a>
                                        </td>
                                        <td>
                                            <a class="block px-3 py-2.5 font-medium text-black dark:text-fg"
                                                href="{{ route('project.show', ['project_uuid' => $project->uuid]) }}"
                                                target="_blank" rel="noopener noreferrer">{{ $project->name }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach (currentTeam()->servers as $server)
                                    <tr class="text-[13px] text-neutral-600 hover:bg-neutral-50 dark:text-fg-dim dark:hover:bg-white/[0.03]">
                                        <td>
                                            <a class="block px-3 py-2.5" href="{{ route('server.show', ['server_uuid' => $server->uuid]) }}"
                                                target="_blank" rel="noopener noreferrer">{{ __('common.server') }}</a>
                                        </td>
                                        <td>
                                            <a class="block px-3 py-2.5 font-medium text-black dark:text-fg"
                                                href="{{ route('server.show', ['server_uuid' => $server->uuid]) }}"
                                                target="_blank" rel="noopener noreferrer">{{ $server->name }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-application.settings-section>
        </div>
    </x-team.settings-layout>
</div>
