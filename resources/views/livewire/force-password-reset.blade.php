<x-auth.shell title="Set a new password"
    description="Your password must be updated before you can continue to the dashboard.">
    <form class="flex flex-col gap-4" wire:submit="submit">
        <x-forms.input id="email" type="email" readonly label="Email" />
        <x-forms.input id="password" type="password" autocomplete="new-password" autofocus label="New password"
            required />
        <x-forms.input id="password_confirmation" type="password" autocomplete="new-password"
            label="Confirm new password" required />

        <div class="auth-guidance">
            <x-reicon name="info-circle" class="mt-0.5 size-4 shrink-0" />
            <p>{{ __('common.password_requirements') }}</p>
        </div>

        <x-forms.button class="w-full justify-center" type="submit" isHighlighted>
            {{ __('auth.reset_password') }}
        </x-forms.button>
    </form>
</x-auth.shell>
