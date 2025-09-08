<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Attendance Chart') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Your existing content is now inside these wrappers --}}
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('Displaying attendance for the last 14 days.') }}</p>

                    <div id="chartData"
                        data-series="{{ json_encode($series) }}"
                        data-days="{{ json_encode($daysOfWeek) }}"
                        class="hidden">
                    </div>

                    <div id="attendanceChart"></div>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find the element containing our data
            const chartDataElement = document.getElementById('chartData');

            // This check prevents errors if the chart logic is on a page without the data element
            if (chartDataElement) {
                // Read and parse the data from the 'data-*' attributes
                const seriesData = JSON.parse(chartDataElement.dataset.series);
                const daysOfWeek = JSON.parse(chartDataElement.dataset.days);

                // Your original, full options object
                const options = {
                    series: seriesData,
                    chart: {
                        height: 600,
                        type: 'rangeBar',
                        toolbar: {
                            show: true,
                        },
                        foreColor: document.documentElement.classList.contains('dark') ? '#E5E7EB' : '#111827'
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            barHeight: '50%',
                            rangeBarGroupRows: true
                        }
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
                        categories: daysOfWeek,
                    },
                    tooltip: {
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            const dataForDay = series[seriesIndex][dataPointIndex];
                            if (!dataForDay || dataForDay.length === 0) {
                                return '';
                            }
                            const dataPoint = dataForDay[0];
                            const seriesName = dataPoint.x;
                            const fromTime = new Date(dataPoint.y[0]);
                            const toTime = new Date(dataPoint.y[1]);
                            const formatTime = (date) => {
                                let hours = date.getHours().toString().padStart(2, '0');
                                let minutes = date.getMinutes().toString().padStart(2, '0');
                                return `${hours}:${minutes}`;
                            }
                            return `
                        <div class="p-2">
                            <div><strong>${seriesName}</strong></div>
                            <div><span>${formatTime(fromTime)} - ${formatTime(toTime)}</span></div>
                        </div>
                        `;
                        }
                    },
                    legend: {
                        position: 'top',
                    },
                    grid: {
                        borderColor: '#90A4AE',
                        strokeDashArray: 2,
                    }
                };

                const chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
                chart.render();
            }
        });
    </script>

</x-app-layout>