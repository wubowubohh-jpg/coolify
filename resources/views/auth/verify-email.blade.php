<x-layout-simple>
    <x-auth.shell title="Coolify" :description="__('auth.verify_email_description')">
        <div class="flex flex-col gap-4">
            <div class="auth-guidance">
                <x-reicon name="mail" class="mt-0.5 size-4 shrink-0" />
                <p>{{ __('auth.verify_email_message') }}</p>
            </div>

            <livewire:verify-email />
        </div>

        <x-slot:footer>
            <span class="text-center">
                <span class="block sm:inline">{{ __('auth.didnt_receive_email') }}</span>
                <span class="block sm:ml-1 sm:inline">{{ __('auth.check_spam_resend') }}</span>
            </span>
        </x-slot:footer>
    </x-auth.shell>
</x-layout-simple>
