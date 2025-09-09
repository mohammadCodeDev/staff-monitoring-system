<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance Chart') }}
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-12">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4">
                <input type="text" id="chart-search-input" placeholder="{{ __('Search Employee...') }}"
                    class="w-full bg-gray-100 dark:bg-gray-900 border-gray-300 dark:border-gray-700 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6 pt-12">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 flex justify-center space-x-2 rtl:space-x-reverse">
                <a href="{{ route('attendances.chart') }}"
                    class="px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendances.chart') ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('Today') }}
                </a>
                <a href="{{ route('attendances.chart.week') }}"
                    class="px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendances.chart.week') ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('This Week') }}
                </a>
            </div>
        </div>
    </div>

    <div class="py-12 pt-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- The title section is updated --}}
                    <div class="flex items-baseline justify-center mb-4">
                        <h3 class="text-lg font-medium">
                            @if($viewType === 'today')
                            {{ __("Today's Attendance Chart") }}
                            @else
                            {{ __("Last 7 Days Attendance Chart") }}
                            @endif
                        </h3>
                        {{-- Add the date range display for the weekly view --}}
                        @if($viewType === 'week' && isset($startDateFormatted) && isset($endDateFormatted))
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400 mx-2">
                            ({{ __('from') }} {{ $startDateFormatted }} {{ __('to') }} {{ $endDateFormatted }})
                        </span>
                        @endif
                    </div>

                    <div id="chartData"
                        data-series="{{ json_encode($series) }}"
                        data-categories="{{ json_encode($categories) }}"
                        data-colors="{{ json_encode($chartColors) }}"
                        data-view-type="{{ $viewType }}"
                        class="hidden">
                    </div>

                    <div id="attendanceChart"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartDataElement = document.getElementById('chartData');
            if (chartDataElement) {
                // Read initial data from the page
                const initialData = {
                    series: JSON.parse(chartDataElement.dataset.series),
                    categories: JSON.parse(chartDataElement.dataset.categories),
                    chartColors: JSON.parse(chartDataElement.dataset.colors),
                };
                const viewType = chartDataElement.dataset.viewType;

                let chart = null; // Chart instance will be stored here

                // This function builds the options object based on the view type
                function buildChartOptions(data) {
                    let options = {
                        series: data.series,
                        colors: data.chartColors,
                        chart: {
                            height: 600,
                            type: 'rangeBar',
                            toolbar: {
                                show: true,
                                autoSelected: 'pan'
                            },
                            animations: {
                                enabled: false
                            },
                            foreColor: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#111827'
                        },
                        xaxis: {
                            type: 'datetime',
                            min: new Date('1970-01-01T00:00:00.000Z').getTime(),
                            max: new Date('1970-01-01T23:59:59.000Z').getTime(),
                            labels: {
                                datetimeUTC: false,
                                format: 'HH:mm'
                            }
                        },
                        yaxis: {
                            categories: data.categories,
                        },
                        grid: {
                            borderColor: '#90A4AE',
                            strokeDashArray: 2
                        }
                    };

                    if (viewType === 'today') {
                        options.plotOptions = {
                            bar: {
                                horizontal: true,
                                distributed: true
                            }
                        };
                        options.legend = {
                            show: false
                        };
                        options.tooltip = {
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            x: {
                                format: 'HH:mm'
                            }
                        };
                    } else { // 'week' view
                        options.plotOptions = {
                            bar: {
                                horizontal: true
                            }
                        };
                        options.legend = {
                            show: true,
                            position: 'bottom'
                        };
                        options.tooltip = {
                            theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                            custom: function({
                                seriesIndex,
                                dataPointIndex,
                                w
                            }) {
                                const seriesName = w.globals.seriesNames[seriesIndex];
                                const dataPoint = w.globals.initialSeries[seriesIndex].data[dataPointIndex];
                                if (!dataPoint || !dataPoint.y) return '';
                                const fromTime = new Date(dataPoint.y[0]);
                                const toTime = new Date(dataPoint.y[1]);
                                const formatTime = (date) => {
                                    let h = date.getHours().toString().padStart(2, '0');
                                    let m = date.getMinutes().toString().padStart(2, '0');
                                    return `${h}:${m}`;
                                };
                                const dayLabel = dataPoint.x;
                                return `<div class="p-2"><div><strong>${seriesName}</strong></div><div><small style="opacity: 0.8;">${dayLabel}</small></div><div><span>${formatTime(fromTime)} - ${formatTime(toTime)}</span></div></div>`;
                            }
                        };
                    }
                    return options;
                }

                function hasRenderableData(series, vType) {
                    if (!series || series.length === 0) return false;
                    if (vType === 'today') return series[0] && series[0].data && series[0].data.length > 0;
                    return series.some(s => s.data && s.data.length > 0);
                }

                const chartContainer = document.querySelector("#attendanceChart");

                function renderChart(data) {
                    chartContainer.innerHTML = ''; // Clear previous content
                    if (!hasRenderableData(data.series, viewType)) {
                        chartContainer.innerHTML = `<div class="text-center p-10">{{ __("No attendance records found.") }}</div>`;
                        return;
                    }
                    const options = buildChartOptions(data);
                    chart = new ApexCharts(chartContainer, options);
                    chart.render();
                }

                // Initial render on page load
                renderChart(initialData);

                // --- LIVE SEARCH LOGIC ---
                let debounceTimer;
                const searchInput = document.getElementById('chart-search-input');
                const apiUrl = viewType === 'today' ? '{{ route("chart.data.today") }}' : '{{ route("chart.data.week") }}';

                searchInput.addEventListener('input', function(e) {
                    clearTimeout(debounceTimer);
                    const searchTerm = e.target.value;
                    debounceTimer = setTimeout(() => {
                        fetch(`${apiUrl}?search=${encodeURIComponent(searchTerm)}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                // Instead of creating a new chart, we just update it with new data
                                if (chart) {
                                    chart.updateOptions(buildChartOptions(data));
                                } else {
                                    renderChart(data); // If chart was destroyed (e.g., no data)
                                }
                            });
                    }, 500); // 500ms debounce delay
                });
            }
        });
    </script>
</x-app-layout>