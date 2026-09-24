@php($meta = $server->server_metadata)
<dl
    class="mt-4 grid gap-x-6 gap-y-5 border-t border-neutral-200 pt-4 sm:grid-cols-2 lg:grid-cols-3 dark:border-white/[0.08]">
    @foreach ([
        __('common.operating_system') => $meta['os'] ?? __('common.not_available'),
        __('common.architecture') => $meta['arch'] ?? __('common.not_available'),
        __('common.kernel') => $meta['kernel'] ?? __('common.not_available'),
        __('common.cpu_cores') => $meta['cpus'] ?? __('common.not_available'),
        __('common.memory') => isset($meta['memory_bytes']) ? round($meta['memory_bytes'] / 1073741824, 1) . ' GB' : __('common.not_available'),
        __('common.docker_version') => $server->dockerVersion() ?? __('common.not_available'),
        __('common.compose_version') => $server->composeVersion() ?? __('common.not_available'),
        __('common.up_since') => $meta['uptime_since'] ?? __('common.not_available'),
    ] as $detailLabel => $detailValue)
        <div>
            <dt class="text-xs font-medium text-neutral-500 dark:text-fg-dim">
                {{ $detailLabel }}
            </dt>
            <dd class="mt-1 text-sm font-medium text-neutral-950 dark:text-fg">
                {{ $detailValue }}
            </dd>
        </div>
    @endforeach
</dl>
