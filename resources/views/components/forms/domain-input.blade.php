@props([
    'id',
    'errorId' => null,
    'hostLabel' => null,
    'hostPlaceholder' => 'app.example.com',
])

@php
    $hostLabel ??= __('common.domain');
    $domainErrorKey = $errorId ?? "{$id}.host";
    $domainError = $errors->first($domainErrorKey);
    $validationLink = null;

    if (filled($domainError)) {
        preg_match('/(https?:\/\/\S+)$/', $domainError, $validationLinkMatches);
        $validationLink = $validationLinkMatches[1] ?? null;
    }
@endphp

<div class="grid gap-4 sm:grid-cols-[8rem_minmax(0,1fr)_8rem]">
    <div class="min-w-0">
        <x-forms.listbox id="{{ $id }}.scheme" htmlId="{{ $id }}-protocol" :label="__('common.protocol')" portal :options="[
            ['value' => 'https', 'label' => 'https'],
            ['value' => 'http', 'label' => 'http'],
        ]" />
    </div>

    <div class="min-w-0">
        <div class="mb-1.5 flex h-4 w-full items-center gap-1.5">
            <label for="{{ $id }}-host" class="mb-0! flex items-center gap-1.5 leading-4">
                {{ $hostLabel }} <x-highlighted text="*" />
            </label>
        </div>
        <input id="{{ $id }}-host" type="text" class="input" wire:model="{{ $id }}.host"
            placeholder="{{ $hostPlaceholder }}" autocomplete="off" required />
        <?php if (filled($domainError)) { ?>
            <p class="mt-1 text-[12px] text-red-500">
                <?php if ($validationLink) { ?>
                    {{ str($domainError)->beforeLast($validationLink)->trim() }}
                    <a class="font-medium underline" href="{{ $validationLink }}">{{ __('common.set_here') }}</a>
                <?php } else { ?>
                    {{ $domainError }}
                <?php } ?>
            </p>
        <?php } ?>
    </div>

    <div class="min-w-0">
        <div class="mb-1.5 flex h-4 w-full items-center gap-1.5">
            <label for="{{ $id }}-port" class="mb-0! flex items-center gap-1.5 leading-4">{{ __('common.port') }}</label>
        </div>
        <input id="{{ $id }}-port" type="number" class="input" wire:model="{{ $id }}.port"
            placeholder="3000" min="1" max="65535" inputmode="numeric" />
    </div>

    <div class="min-w-0 sm:col-span-3">
        <div class="mb-1.5 flex h-4 w-full items-center gap-1.5">
            <label for="{{ $id }}-path" class="mb-0! flex items-center gap-1.5 leading-4">{{ __('common.path') }}</label>
        </div>
        <input id="{{ $id }}-path" type="text" class="input" wire:model="{{ $id }}.path"
            placeholder="/api/v3" autocomplete="off" />
        <p class="mt-1 text-[12px] text-neutral-500 dark:text-fg-dim">
            {{ __('common.optional_path_description') }}
        </p>
    </div>
</div>
