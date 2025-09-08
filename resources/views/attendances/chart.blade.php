<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance Chart') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- START: View Toggle Buttons --}}
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">
                            @if($viewType === 'today')
                            {{ __("Today's Attendance Chart") }}
                            @else
                            {{ __("Last 7 Days Attendance Chart") }}
                            @endif
                        </h3>
                        <div class="flex space-x-2 rtl:space-x-reverse">
                            <a href="{{ route('attendances.chart') }}"
                                class="px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendances.chart') ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                                {{ __('Today') }}
                            </a>
                            <a href="{{ route('attendances.chart.week') }}"
                                class="px-4 py-2 text-sm font-medium rounded-md {{ request()->routeIs('attendances.chart.week') ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }}">
                                {{ __('This Week') }}
                            </a>
                        </div>
                    </div>
                    {{-- END: View Toggle Buttons --}}

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
                        foreColor: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#111827'
                    },
                    xaxis: {
                        type: 'datetime',
                        min: new Date('1970-01-01T00:00:00').getTime(),
                        max: new Date('1970-01-01T23:59:59').getTime(),
                        labels: {
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
                            horizontal: true,
                            rangeBarGroupRows: true
                        }
                    };
                    options.legend = {
                        show: true,
                        position: 'bottom'
                    };
                    options.tooltip = {
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            const seriesName = w.globals.seriesNames[seriesIndex];
                            if (!series[seriesIndex][dataPointIndex] || series[seriesIndex][dataPointIndex].length === 0) return '';
                            const fromTime = new Date(series[seriesIndex][dataPointIndex][0].y[0]);
                            const toTime = new Date(series[seriesIndex][dataPointIndex][0].y[1]);
                            const formatTime = (date) => {
                                let hours = date.getHours().toString().padStart(2, '0');
                                let minutes = date.getMinutes().toString().padStart(2, '0');
                                return `${hours}:${minutes}`;
                            }
                            return `<div class="p-2"><div><strong>${seriesName}</strong></div><div><span>${formatTime(fromTime)} - ${formatTime(toTime)}</span></div></div>`;
                        }
                    };
                }

                if (seriesData.length === 0 || seriesData[0].data.length === 0) {
                    document.querySelector("#attendanceChart").innerHTML = `<div class="text-center p-10">{{ __("No attendance records found.") }}</div>`;
                } else {
                    const chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
                    chart.render();
                }
            }
        });
    </script>
</x-app-layout>