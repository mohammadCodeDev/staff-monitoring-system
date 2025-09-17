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
                    {{-- STEP 1: Add data-* attributes to the chart div --}}
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
            // STEP 2: Read data from the HTML element
            const chartElement = document.querySelector("#chart");
            const seriesData = JSON.parse(chartElement.dataset.series);
            const categoriesData = JSON.parse(chartElement.dataset.categories);

            const options = {
                series: seriesData,
                chart: {
                    height: 800,
                    type: 'rangeBar',
                    zoom: {
                        enabled: true
                    },
                    fontFamily: 'inherit',
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: true,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: true,
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '50%',
                        rangeBarGroupRows: true
                    }
                },
                colors: ['#000000', '#28a745', '#dc3545'],
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
                markers: {
                    size: 6,
                    strokeWidth: 0,
                    hover: {
                        size: 8
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

            const chart = new ApexCharts(document.querySelector("#chart"), options);
            chart.render();
        });
    </script>
    @endpush
</x-app-layout>