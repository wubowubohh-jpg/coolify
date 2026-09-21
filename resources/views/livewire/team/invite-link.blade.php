<div>
    @can('manageInvitations', currentTeam())
        <form wire:submit="viaLink">
            <x-application.settings-section :title="__('common.invite_member')"
                :description="__('common.invite_member_description')">
                <x-slot:actions>
                    @if (is_transactional_emails_enabled())
                        <x-forms.button type="button" wire:click.prevent="viaEmail">
                            <x-reicon name="notifications" class="size-3.5" />
                            {{ __('common.send_email') }}
                        </x-forms.button>
                    @endif
                    <x-forms.button type="submit" wire:target="viaLink"
                        defaultClass="button button-highlighted">
                        <x-reicon name="plus" class="size-3.5" />
                        {{ __('common.generate_link') }}
                    </x-forms.button>
                </x-slot:actions>

                @if (!is_transactional_emails_enabled() && isInstanceAdmin())
                    <x-callout type="warning" :title="__('common.email_delivery_not_configured')">
                        {{ __('common.configure_transactional_email_invites') }}
                    </x-callout>
                @endif

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <x-forms.input id="email" type="email" :label="__('common.email_address')"
                        placeholder="teammate@example.com" required />
                    <x-forms.listbox id="role" :label="__('common.role')" :options="array_values(array_filter([
                        auth()->user()->role() === 'owner' ? ['value' => 'owner', 'label' => __('common.owner')] : null,
                        ['value' => 'admin', 'label' => __('common.admin')],
                        ['value' => 'member', 'label' => __('common.member')],
                    ]))" />
                </div>
            </x-application.settings-section>
        </form>
    @endcan
</div>
