<div x-data x-init="$nextTick(() => { if ($refs.autofocusInput) $refs.autofocusInput.focus(); })"
    class="mt-8 w-full max-w-[920px] lg:mt-3">
    <form wire:submit="submit">
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.docker_image') }}</h2>
                    <p>{{ __('common.docker_image_description') }}</p>
                </div>
                <x-forms.button type="submit" isHighlighted>{{ __('common.create_application') }}</x-forms.button>
            </div>
            <div class="application-settings-section-body space-y-4">
                <x-forms.input id="imageName" :label="__('common.image_name')"
                    placeholder="nginx, ghcr.io/user/app:v1.2.3, or nginx:stable@sha256:…"
                    :helper="__('common.image_reference_helper')"
                    required autofocus />
                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto_minmax(0,1fr)] sm:items-end"
                    :aria-label="__('common.tag_digest_exclusive')">
                    <x-forms.input id="imageTag" :label="__('common.tag')" placeholder="latest"
                        :helper="__('common.tag_helper')" />
                    <div
                        class="flex items-center justify-center text-xs font-semibold text-neutral-400 sm:h-9 dark:text-fg-faint">
                        <span>{{ __('common.or') }}</span>
                    </div>
                    <x-forms.input id="imageSha256" :label="__('common.sha256_digest')"
                        placeholder="59e02939b1bf39f16c93138a28727aec…"
                        :helper="__('common.digest_helper')" />
                </div>
            </div>
        </section>
    </form>
</div>
