<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Monthly Attendance Report for') }}: {{ $employee->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div id="chart"
                        data-series='@json($chartSeries)'
                        data-categories='@json($chartCategories)'>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartElement = document.querySelector("#chart");

            try {
                const seriesData = JSON.parse(chartElement.dataset.series);
                const categoriesData = JSON.parse(chartElement.dataset.categories);

                const hasData = seriesData.some(series => series.data && series.data.length > 0);

                if (!hasData) {
                    chartElement.innerHTML = `<div class="text-center py-8 text-gray-500">{{ __('No attendance records found for this month.') }}</div>`;
                    return;
                }

                const options = {
                    series: seriesData,
                    chart: {
                        height: 800,
                        type: 'rangeBar', // We explicitly set the type to rangeBar for all series
                        zoom: {
                            enabled: true
                        },
                        fontFamily: 'inherit',
                        toolbar: {
                            show: true,
                            autoSelected: 'pan'
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            barHeight: '50%',
                            rangeBarGroupRows: true
                        }
                    },
                    colors: ['#000000', '#28a745', '#dc3545'], // Black, Green, Red
                    fill: {
                        type: 'solid'
                    },
                    xaxis: {
                        type: 'datetime',
                        min: new Date('1970-01-01T00:00:00.000Z').getTime(),
                        max: new Date('1970-01-01T23:59:59.000Z').getTime(),
                        labels: {
                            datetimeUTC: false,
                            formatter: function(val) {
                                const date = new Date(val);
                                return date.toLocaleTimeString('fa-IR', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                });
                            },
                            style: {
                                colors: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#374151'
                            }
                        },
                        title: {
                            text: 'ساعت',
                            style: {
                                color: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#374151'
                            }
                        }
                    },
                    yaxis: {
                        categories: categoriesData,
                        labels: {
                            style: {
                                colors: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#374151'
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            colors: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#374151'
                        }
                    },
                    tooltip: {
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        x: {
                            show: true,
                            formatter: function(val) {
                                const date = new Date(val);
                                return date.toLocaleTimeString('fa-IR', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    second: '2-digit',
                                    hour12: false
                                });
                            }
                        }
                    }
                };

                const chart = new ApexCharts(chartElement, options);
                chart.render();

            } catch (error) {
                chartElement.innerHTML = `<div class="text-center py-8 text-red-500"><strong>{{ __('Error rendering chart:') }}</strong></div>`;
                console.error("Chart Error:", error);
            }
        });
    </script>
    @endpush
</x-app-layout>