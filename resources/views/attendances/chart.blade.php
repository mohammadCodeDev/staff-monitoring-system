<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance Chart') }}
        </h2>
    </x-slot>

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
                const seriesData = JSON.parse(chartDataElement.dataset.series);
                const categoriesData = JSON.parse(chartDataElement.dataset.categories);
                const colorsData = JSON.parse(chartDataElement.dataset.colors);
                const viewType = chartDataElement.dataset.viewType;

                let options = {
                    series: seriesData,
                    colors: colorsData,
                    chart: {
                        height: 600,
                        type: 'rangeBar',
                        toolbar: {
                            show: true,
                            autoSelected: 'pan'
                        },
                        // Animations are now re-enabled as you requested
                        // animations: { enabled: false }, // This line is removed
                        foreColor: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#111827'
                    },
                    xaxis: {
                        type: 'datetime',
                        // Add 'Z' back to define the axis range in UTC
                        min: new Date('1970-01-01T00:00:00.000Z').getTime(),
                        max: new Date('1970-01-01T23:59:59.000Z').getTime(),
                        labels: {
                            // This remains FALSE so the labels are shown in the user's local time
                            datetimeUTC: false,
                            format: 'HH:mm'
                        }
                    },
                    yaxis: {
                        categories: categoriesData,
                    },
                    grid: {
                        borderColor: '#90A4AE',
                        strokeDashArray: 2,
                    }
                };

                // Apply specific options based on the view type
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
                                let hours = date.getHours().toString().padStart(2, '0');
                                let minutes = date.getMinutes().toString().padStart(2, '0');
                                return `${hours}:${minutes}`;
                            };
                            const dayLabel = dataPoint.x;
                            return `<div class="p-2"><div><strong>${seriesName}</strong></div><div><small style="opacity: 0.8;">${dayLabel}</small></div><div><span>${formatTime(fromTime)} - ${formatTime(toTime)}</span></div></div>`;
                        }
                    };
                }

                function hasRenderableData(series, viewType) {
                    if (!series || series.length === 0) return false;
                    if (viewType === 'today') {
                        return series[0] && series[0].data && series[0].data.length > 0;
                    } else { // 'week' view
                        return series.some(s => s.data && s.data.length > 0);
                    }
                }

                if (!hasRenderableData(seriesData, viewType)) {
                    document.querySelector("#attendanceChart").innerHTML = `<div class="text-center p-10">{{ __("No attendance records found.") }}</div>`;
                } else {
                    const chart = new ApexCharts(document.querySelector("#attendanceChart"), options);

                    // START: FINAL WORKAROUND FOR THE RENDERING BUG
                    // We add a small delay before rendering the chart.
                    // This gives the browser enough time to calculate the container's final dimensions.
                    setTimeout(function() {
                        chart.render();
                    }, 150); // 150 milliseconds delay
                    // END: FINAL WORKAROUND
                }
            }
        });
    </script>
</x-app-layout>