<div class="flex w-full flex-col gap-4">
    <div
        class="flex items-start gap-3 rounded-lg bg-neutral-100 px-3 py-2.5 text-sm text-neutral-600 dark:bg-white/[0.04] dark:text-fg-dim">
        <div
            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-neutral-500 shadow-[0_0_0_1px_var(--coollabs-hairline)] dark:bg-raised dark:text-fg-dim">
            <x-reicon name="feedback" class="size-4" />
        </div>
        <div class="min-w-0">
            <p class="font-medium text-black dark:text-fg">{{ __('common.tell_need') }}</p>
            <p class="mt-0.5 text-xs leading-5">{{ __('common.feedback_context') }}</p>
        </div>
    </div>

    <form wire:submit="submit" class="flex flex-col gap-4">
        <x-forms.input minlength="3" required id="subject" :label="__('common.subject')"
            :placeholder="__('common.feedback_short_summary')" autofocus />
        <x-forms.textarea minlength="10" maxlength="1000" required rows="8" id="description" :label="__('common.details')"
            class="font-sans" spellcheck
            :placeholder="__('common.feedback_prompt')" />

        <div
            class="flex flex-col-reverse items-stretch justify-between gap-3 border-t border-neutral-200 pt-4 sm:flex-row sm:items-center dark:border-white/[0.06]">
            <p class="text-xs text-neutral-500 dark:text-fg-faint">{{ __('common.replies_account_email') }}</p>
            <x-forms.button class="justify-center sm:min-w-28" type="submit" isHighlighted>
                {{ __('common.send_feedback') }}
            </x-forms.button>
        </div>
    </form>
</div>
