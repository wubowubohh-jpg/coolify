@php
    $isSuggested = (bool) ($row['is_suggested'] ?? false);
    $dnsType = match ($row['dns_status']) {
        'ok' => 'success',
        'failed' => 'error',
        'skipped' => 'warning',
        default => 'neutral',
    };
    $dnsLabel = match ($row['dns_status']) {
        'ok' => __('common.dns_matches'),
        'failed' => __('common.dns_mismatch'),
        'skipped' => __('common.dns_skipped'),
        'checking' => __('common.checking_dns'),
        'pending' => __('common.not_checked'),
        default => __('common.dns_unknown'),
    };
    $gridClass = 'service-domains-overview-grid';
    $publicUrl = getFqdnWithoutPort($row['url']);
    $domainParts = $isSuggested ? null : parse_url($publicUrl);
    $faviconUrl = is_array($domainParts) && isset($domainParts['scheme'], $domainParts['host'])
        ? $domainParts['scheme'].'://'.$domainParts['host'].'/favicon.ico'
        : null;
    $rowDirection = $isCompose
        ? ($serviceRedirects[$this->serviceRedirectWireKey($row['service'])] ?? 'both')
        : $redirect;
    $isNoindexed = $application->isDomainNoindexed($row['url']);
    $domainKey = hash('sha256', $row['url'].'|'.($row['service'] ?? ''));
    $editingParts = \App\Support\DomainUrlParts::split($row['url']);
    if ($row['has_port_override'] ?? false) {
        $editingParts['port'] = (string) $row['internal_port'];
    }
@endphp

<div wire:key="domain-row-{{ md5(($isSuggested ? 's:' : '') . $row['url'] . '|' . ($row['service'] ?? '')) }}"
    x-show="matchesDomainSearch(@js(($row['service'] ?? '').' '.$row['url']))" class="env-table-item">
    <div @class([
        'data-table-row',
        $gridClass,
        'domains-row-suggested' => $isSuggested,
    ])>
        <div class="flex min-w-0 flex-col gap-1">
            <div class="flex min-w-0 items-center gap-2">
                @if ($isSuggested)
                    <span
                        class="min-w-0 text-[13px] text-black sm:truncate dark:text-white"
                        title="{{ $row['url'] }} (not configured yet)">
                        {{ $row['url'] }}
                    </span>
                @else
                    @if ($faviconUrl)
                        <span class="relative size-4 shrink-0" aria-hidden="true">
                            <x-reicon name="globe"
                                class="domain-favicon-fallback size-4 text-neutral-400 dark:text-fg-faint" />
                            <img src="{{ $faviconUrl }}" alt="" loading="lazy" decoding="async"
                                referrerpolicy="no-referrer"
                                x-init="if ($el.complete && $el.naturalWidth > 0) { $el.previousElementSibling.classList.add('hidden'); $el.classList.remove('invisible') }"
                                x-on:load="$el.previousElementSibling.classList.add('hidden'); $el.classList.remove('invisible')"
                                x-on:error="$el.remove()"
                                class="invisible absolute inset-0 size-4 rounded-sm" />
                        </span>
                    @endif
                    <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer"
                        class="min-w-0 flex-1 truncate text-[13px] text-black underline decoration-neutral-300 underline-offset-2 hover:decoration-coollabs dark:text-fg dark:decoration-white/20 dark:hover:decoration-warning"
                        title="{{ $publicUrl }}">
                        {{ $publicUrl }}
                    </a>
                @endif
                @if ($isSuggested && ! empty($row['suggestion_label']))
                    <span class="table-badge table-badge-warning shrink-0">{{ $row['suggestion_label'] }}</span>
                @endif
            </div>
            @if ($isSuggested && filled($row['dns_message']))
                <p class="text-[12px] leading-4 text-amber-700 sm:truncate dark:text-amber-400/90"
                    title="{{ $row['dns_message'] }}">
                    {{ $row['dns_message'] }}
                </p>
            @endif
        </div>

        <div class="service-domain-detail" title="{{ __('common.protocol_redirect') }}">
            <span class="service-domain-detail-label">{{ __('common.protocol_redirect') }}</span>
            <span>{{ str_starts_with($row['url'], 'https://') && $isForceHttpsEnabled ? __('common.http_to_https') : __('common.disabled') }}</span>
        </div>
        <div class="service-domain-detail" title="{{ __('common.domain_redirect') }}">
            <span class="service-domain-detail-label">{{ __('common.domain_redirect') }}</span>
            <span>{{ match ($rowDirection) { 'www' => __('common.non_www_to_www'), 'non-www' => __('common.www_to_non_www'), default => __('common.disabled') } }}</span>
        </div>
        <div class="service-domain-detail"
            title="{{ ($row['has_port_override'] ?? false) ? 'Custom internal port for this domain' : 'Inherited from the application or Compose service port' }}">
            <span class="service-domain-detail-label">{{ __('common.internal_port') }}</span>
            @if (filled($row['internal_port'] ?? null))
                <span aria-label="Internal port {{ $row['internal_port'] }}">{{ $row['internal_port'] }}</span>
            @else
                <span role="img" aria-label="No internal port" title="No internal port. Set Ports Exposes or a per-domain internal port so the proxy can route this domain." class="text-red-500 dark:text-red-400">
                    <x-reicon name="alert-triangle" class="size-4" />
                </span>
            @endif
        </div>
        <div class="service-domain-detail">
            <span class="service-domain-detail-label">{{ __('common.search_indexing') }}</span>
            <span role="img" aria-label="{{ $isNoindexed ? __('common.search_indexing_blocked') : __('common.search_indexing_allowed') }}"
                title="{{ $isNoindexed ? __('common.search_indexing_blocked') : __('common.search_indexing_allowed') }}">
                <x-reicon :name="$isNoindexed ? 'x' : 'check'" class="size-4" />
            </span>
        </div>

        <div class="service-domain-mobile-summary" aria-label="{{ __('common.domain_routing_summary') }}">
            @if (str_starts_with($row['url'], 'https://') && $isForceHttpsEnabled)
                <span>{{ __('common.http_to_https') }}</span>
            @endif
            @if (in_array($rowDirection, ['www', 'non-www'], true))
                <span>{{ $rowDirection === 'www' ? __('common.non_www_to_www') : __('common.www_to_non_www') }}</span>
            @elseif (! str_starts_with($row['url'], 'https://') || ! $isForceHttpsEnabled)
                <span>{{ __('common.no_redirects') }}</span>
            @endif
            <span>{{ __('common.port') }} {{ $row['internal_port'] ?? __('common.missing') }}</span>
            <span>{{ $isNoindexed ? __('common.noindex') : __('common.indexable') }}</span>
        </div>

        <div class="service-domain-dns flex min-w-0 items-center">
            @if ($row['dns_status'] === 'failed')
                <x-status-badge as="button" @click="$dispatch('open-dns-records-modal')" :status="$dnsLabel" :type="$dnsType"
                    :title="__('common.view_dns_records_to_fix')" class="cursor-pointer hover:bg-neutral-200 dark:hover:bg-white/[0.1]" />
            @elseif ($row['dns_status'] === 'checking')
                <x-status-badge dynamic :title="$row['dns_message']">
                    <x-loading compact :aria-label="__('common.checking_dns')" />
                    <span class="truncate">{{ __('common.checking_dns') }}</span>
                </x-status-badge>
            @else
                <x-status-badge :status="$dnsLabel" :type="$dnsType"
                    :title="$row['dns_status'] === 'ok' ? null : $row['dns_message']" />
            @endif
        </div>

        <div class="service-domain-actions flex items-center justify-end gap-1">
            @can('update', $application)
                <button type="button" wire:click="checkDomainDns({{ $index }})"
                    wire:loading.attr="disabled"
                    wire:target="checkDomainDns({{ $index }}),checkAllDns"
                        class="icon-button shrink-0" title="{{ __('common.check_dns') }}" aria-label="{{ __('common.check_dns') }}">
                    <x-reicon name="refresh" class="size-3.5" />
                </button>
                @unless ($labelsAreWritable)
                    @if ($isSuggested)
                        @if ($row['needs_force_add'] ?? false)
                            <x-forms.button wire:click="addSuggestedDomain({{ $index }})" isError class="h-7! px-2! text-[12px]!">
                                {{ __('common.continue') }}
                            </x-forms.button>
                        @else
                            <x-forms.button wire:click="addSuggestedDomain({{ $index }})" isHighlighted class="h-7! shrink-0 px-2.5! text-[12px]!">
                                {{ __('common.add_domain') }}
                            </x-forms.button>
                        @endif
                    @else
                        <button type="button"
                            @click="openEditDomain(@js($index), @js($row['url']), @js($editingParts), @js($row['service']), @js($isNoindexed ? 'noindex' : 'index'), @js($rowDirection))"
                            class="icon-button shrink-0"
                            title="{{ __('common.domain_settings') }}" aria-label="{{ __('common.domain_settings') }}: {{ $publicUrl }}">
                            <x-reicon name="settings" class="size-3.5" />
                        </button>
                        <x-modal-confirmation class="!w-auto shrink-0" :title="__('common.remove_domain_question')" :buttonTitle="__('common.remove')"
                            isErrorButton submitAction="removeDomainByKey({{ $domainKey }})" :actions="[
                                __('common.domain_removed_from_resource'),
                                __('common.redeploy_proxy_changes'),
                            ]" :confirmWithPassword="false" :confirmWithText="false" :step2ButtonText="__('common.remove_domain')">
                            <x-slot:trigger>
                                <button type="button" class="icon-button shrink-0 text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
                                    title="{{ __('common.remove_domain') }}" aria-label="{{ __('common.remove_domain') }}">
                                    <x-reicon name="trash" class="size-3.5" />
                                </button>
                            </x-slot:trigger>
                        </x-modal-confirmation>
                    @endif
                @endunless
            @endcan
        </div>
    </div>
</div>
