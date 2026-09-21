<div>
    <x-slot:title>
        {{ data_get_str($server, 'name')->limit(10) }} > Metrics | Coolify
    </x-slot>

    <livewire:server.navbar :server="$server" />

    <div
        class="server-settings-workspace application-settings-workspace mt-4 grid w-full max-w-none min-w-0 gap-8 lg:mt-0 xl:grid-cols-[210px_minmax(0,1fr)] xl:gap-8">
        <x-server.sidebar :server="$server" activeMenu="metrics" />

        <div class="application-settings-form flex w-full flex-col gap-6"
            @if ($server->isMetricsEnabled()) x-init="$wire.loadData()"
                @if ($poll) wire:poll.5000ms="pollData" @endif
            @endif>
            @if ($server->isMetricsEnabled())
                <x-application.settings-section id="server-metrics-overview-section" :title="__('common.metrics')"
                    :helper="__('common.metrics_overview_helper')">
                    <x-slot:actions>
                        <div class="flex items-center gap-2">
                            <x-status-badge :status="$poll ? __('common.live_updates') : __('common.historical_range')"
                                :type="$poll ? 'success' : 'neutral'" />
                            <x-forms.button canGate="update" :canResource="$server" wire:click="toggleMetrics">
                                {{ __('common.disable_metrics') }}
                            </x-forms.button>
                        </div>
                    </x-slot:actions>

                    <div class="max-w-xs">
                        <x-forms.listbox id="interval" :label="__('common.time_range')" onChange="setInterval" :options="[
                            ['value' => 5, 'label' => __('common.last_5_minutes_live')],
                            ['value' => 10, 'label' => __('common.last_10_minutes_live')],
                            ['value' => 30, 'label' => __('common.last_30_minutes')],
                            ['value' => 60, 'label' => __('common.last_hour')],
                            ['value' => 720, 'label' => __('common.last_12_hours')],
                            ['value' => 10080, 'label' => __('common.last_week')],
                            ['value' => 43200, 'label' => __('common.last_30_days')],
                        ]" />
                    </div>
                    <p class="mt-3 text-xs leading-5 text-neutral-500 dark:text-fg-dim">
                        {{ __('common.metrics_live_refresh_helper') }}
                    </p>
                </x-application.settings-section>

                <x-application.settings-section id="server-cpu-metrics-section" :title="__('common.cpu_usage')"
                    :helper="__('common.cpu_usage_helper')">
                    <div wire:ignore id="{!! $chartId !!}-cpu" class="min-h-[240px] w-full"></div>
                </x-application.settings-section>

                <x-application.settings-section id="server-memory-metrics-section" :title="__('common.memory_usage')"
                    :helper="__('common.memory_usage_helper')">
                    <div wire:ignore id="{!! $chartId !!}-memory" class="min-h-[240px] w-full"></div>
                </x-application.settings-section>

                @script
                    <script>
                        (() => {
                            checkTheme();

                            const formatPercent = value => {
                                const number = Number(value);
                                const precision = Math.abs(number) < 1 ? 2 : 1;

                                return `${Number(number.toFixed(precision))}%`;
                            };

                            const formatTimestamp = timestamp => {
                                const date = new Date(timestamp);

                                return `${date.toLocaleString(undefined, {
                                    timeZone: 'UTC',
                                    hour12: false
                                })} UTC`;
                            };

                            const chartOptions = (name, color, loadingText) => ({
                                chart: {
                                    height: 240,
                                    type: 'area',
                                    toolbar: {
                                        show: false
                                    },
                                    zoom: {
                                        enabled: false
                                    },
                                    animations: {
                                        enabled: true
                                    },
                                    background: 'transparent',
                                },
                                series: [{
                                    name,
                                    data: [],
                                }],
                                colors: [color],
                                stroke: {
                                    curve: 'smooth',
                                    width: 2,
                                },
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        opacityFrom: 0.28,
                                        opacityTo: 0.02,
                                        stops: [0, 90, 100],
                                    },
                                },
                                dataLabels: {
                                    enabled: false,
                                },
                                grid: {
                                    borderColor: 'rgba(128, 128, 128, 0.14)',
                                    strokeDashArray: 4,
                                },
                                legend: {
                                    show: false,
                                },
                                xaxis: {
                                    type: 'datetime',
                                    labels: {
                                        datetimeUTC: true,
                                        style: {
                                            colors: textColor,
                                        },
                                    },
                                },
                                yaxis: {
                                    min: 0,
                                    max: max => max > 0 ? max * 1.2 : 1,
                                    forceNiceScale: true,
                                    tickAmount: 4,
                                    labels: {
                                        style: {
                                            colors: textColor,
                                        },
                                        formatter: formatPercent,
                                    },
                                },
                                noData: {
                                    text: loadingText,
                                    style: {
                                        color: textColor,
                                    },
                                },
                                tooltip: {
                                    shared: false,
                                    intersect: false,
                                    followCursor: false,
                                    fixed: {
                                        enabled: false,
                                    },
                                    marker: {
                                        show: false,
                                    },
                                    custom: ({
                                        series,
                                        seriesIndex,
                                        dataPointIndex,
                                        w
                                    }) => {
                                        const value = series[seriesIndex][dataPointIndex];
                                        const timestamp = w.globals.seriesX[seriesIndex][dataPointIndex];

                                        return `<div class="apexcharts-tooltip-custom">
                                            <div class="apexcharts-tooltip-custom-value">${name}: <span class="apexcharts-tooltip-value-bold">${formatPercent(value)}</span></div>
                                            <div class="apexcharts-tooltip-custom-title">${formatTimestamp(timestamp)}</div>
                                        </div>`;
                                    },
                                },
                            });

                            const cpuChart = new ApexCharts(
                                document.getElementById('{!! $chartId !!}-cpu'),
                                chartOptions(@js(__('common.cpu')), cpuColor, @js(__('common.loading_cpu_metrics'))),
                            );
                            const memoryChart = new ApexCharts(
                                document.getElementById('{!! $chartId !!}-memory'),
                                chartOptions(@js(__('common.memory')), ramColor, @js(__('common.loading_memory_metrics'))),
                            );

                            cpuChart.render();
                            memoryChart.render();

                            Livewire.on('refreshChartData-{!! $chartId !!}-cpu', chartData => {
                                checkTheme();
                                cpuChart.updateOptions({
                                    colors: [cpuColor],
                                    series: [{
                                        name: @js(__('common.cpu')),
                                        data: chartData[0].seriesData,
                                    }],
                                    xaxis: {
                                        type: 'datetime',
                                        labels: {
                                            datetimeUTC: true,
                                            style: {
                                                colors: textColor,
                                            },
                                        },
                                    },
                                    yaxis: {
                                        min: 0,
                                        max: max => max > 0 ? max * 1.2 : 1,
                                        forceNiceScale: true,
                                        tickAmount: 4,
                                        labels: {
                                            style: {
                                                colors: textColor,
                                            },
                                            formatter: formatPercent,
                                        },
                                    },
                                    noData: {
                                        text: @js(__('common.no_cpu_metrics')),
                                        style: {
                                            color: textColor,
                                        },
                                    },
                                });
                            });

                            Livewire.on('refreshChartData-{!! $chartId !!}-memory', chartData => {
                                checkTheme();
                                memoryChart.updateOptions({
                                    colors: [ramColor],
                                    series: [{
                                        name: @js(__('common.memory')),
                                        data: chartData[0].seriesData,
                                    }],
                                    xaxis: {
                                        type: 'datetime',
                                        labels: {
                                            datetimeUTC: true,
                                            style: {
                                                colors: textColor,
                                            },
                                        },
                                    },
                                    yaxis: {
                                        min: 0,
                                        max: max => max > 0 ? max * 1.2 : 1,
                                        forceNiceScale: true,
                                        tickAmount: 4,
                                        labels: {
                                            style: {
                                                colors: textColor,
                                            },
                                            formatter: formatPercent,
                                        },
                                    },
                                    noData: {
                                        text: @js(__('common.no_memory_metrics')),
                                        style: {
                                            color: textColor,
                                        },
                                    },
                                });
                            });
                        })();
                    </script>
                @endscript
            @elseif ($server->isSentinelEnabled())
                <x-application.settings-section id="server-metrics-overview-section" :title="__('common.metrics')"
                    :helper="__('common.metrics_overview_helper')">
                    <x-empty size="sm" :title="__('common.metrics_disabled')"
                        :description="__('common.enable_metrics_description')"
                        icon-name="dashboard">
                        <x-slot:contents>
                            <x-forms.button canGate="update" :canResource="$server" isHighlighted
                                wire:click="toggleMetrics">
                                {{ __('common.enable_metrics') }}
                            </x-forms.button>
                        </x-slot:contents>
                    </x-empty>
                </x-application.settings-section>
            @else
                <x-application.settings-section id="server-metrics-overview-section" :title="__('common.metrics')"
                    :helper="__('common.metrics_overview_helper')">
                    <x-empty size="sm" :title="__('common.metrics_unavailable')"
                        :description="__('common.sentinel_metrics_unavailable')"
                        icon-name="dashboard">
                        <x-slot:contents>
                            <a class="button"
                                href="{{ route('server.sentinel', ['server_uuid' => $server->uuid]) }}"
                                {{ wireNavigate() }}>
                                {{ __('common.view_sentinel') }}
                                <x-external-link />
                            </a>
                        </x-slot:contents>
                    </x-empty>
                </x-application.settings-section>
            @endif
        </div>
    </div>
</div>
