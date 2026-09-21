<div class="mt-8 w-full max-w-none lg:mt-3">
    <form wire:submit="submit">
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.dockerfile') }}</h2>
                    <p>{{ __('common.dockerfile_description') }}</p>
                </div>
                <x-forms.button type="submit" wire:target="submit" isHighlighted>{{ __('common.create_application') }}</x-forms.button>
            </div>
            <div class="application-settings-section-body p-0!">
                <x-forms.textarea useMonacoEditor monacoEditorLanguage="dockerfile" rows="20"
                    id="dockerfile" autofocus placeholder='FROM nginx
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
'></x-forms.textarea>
            </div>
        </section>
    </form>
</div>
