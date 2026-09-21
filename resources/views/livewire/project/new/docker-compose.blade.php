<div class="mt-8 w-full lg:mt-3">
    <form wire:submit="submit">
        <section class="application-settings-section">
            <div class="application-settings-section-header">
                <div>
                    <h2>{{ __('common.docker_compose_title') }}</h2>
                    <p>{{ __('common.compose_service_description') }}</p>
                </div>
                <x-forms.button type="submit" wire:target="submit" isHighlighted>{{ __('common.create_service') }}</x-forms.button>
            </div>
            <div class="application-settings-section-body">
                <x-forms.textarea useMonacoEditor monacoEditorLanguage="yaml" :label="__('common.docker_compose_file')"
                    rows="20" id="dockerComposeRaw" autofocus placeholder='services:
  app:
    image: nginx:alpine
    ports:
      - "80"
'></x-forms.textarea>
            </div>
        </section>
    </form>
</div>
