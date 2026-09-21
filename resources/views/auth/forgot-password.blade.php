<x-layout-simple>
    <x-auth.shell title="Coolify" :description="__('auth.forgot_description')">
        <div class="flex flex-col gap-4">
            @if (session('status'))
                <x-auth.alert type="success">{{ session('status') }}</x-auth.alert>
            @endif

            @if ($errors->any())
                <x-auth.alert type="error">
                    <div class="flex flex-col gap-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </x-auth.alert>
            @endif

            @if (is_transactional_emails_enabled())
                <form action="/forgot-password" method="POST" class="flex flex-col gap-4">
                    @csrf
                    <x-forms.input required type="email" name="email" autocomplete="email" autofocus
                        label="{{ __('input.email') }}" />
                    <x-forms.button class="w-full justify-center" type="submit" isHighlighted>
                        {{ __('auth.forgot_password_send_email') }}
                    </x-forms.button>
                </form>
            @else
                <x-auth.alert type="warning">
                    <p class="font-medium">{{ __('auth.transactional_email_not_configured') }}</p>
                    <p class="mt-0.5 text-black/70 dark:text-white/70">
                        {{ __('auth.configure_email_manual_reset') }}
                        <a class="font-medium underline underline-offset-2" target="_blank" rel="noopener noreferrer"
                            href="{{ config('constants.urls.docs') }}">{{ __('auth.manual_reset_guide') }}</a>.
                    </p>
                </x-auth.alert>
            @endif
        </div>

        <x-slot:footer>
            <span>{{ __('auth.remember_password') }}</span>
            <a href="/login" class="auth-text-link">{{ __('auth.back_to_login') }}</a>
        </x-slot:footer>
    </x-auth.shell>
</x-layout-simple>
