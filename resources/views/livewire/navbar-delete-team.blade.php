<div class="w-full min-w-0">
    @if (auth()->user()->roleInTeam(currentTeam()->id) === 'owner')
    <x-modal-confirmation :title="__('common.confirm_team_deletion')" buttonFullWidth isErrorButton submitAction="delete"
        :actions="[__('common.team_will_be_permanently_deleted')]" :confirmationText="$team"
        :confirmationLabel="__('common.confirm_team_name')"
        :shortConfirmationLabel="__('common.team_name')">
        <x-slot:trigger>
            <button type="button" title="{{ __('common.delete_team') }}" aria-label="{{ __('common.delete_team') }}"
                class="menu-item justify-start text-left !text-error hover:!bg-error/10 dark:!text-error dark:hover:!bg-error/15">
                <x-reicon name="trash" class="menu-item-icon" />
                <span class="menu-item-label sidebar-collapsed-label text-left">{{ __('common.delete_team') }}</span>
            </button>
        </x-slot:trigger>
    </x-modal-confirmation>
    @endif
</div>
