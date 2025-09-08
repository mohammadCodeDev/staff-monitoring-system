<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Today's Attendance Chart") }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __("Displaying today's entry and exit times.") }}</p>

                    {{-- The div now receives 'categories' AND 'colors' --}}
                    <div id="chartData"
                        data-series="{{ json_encode($series) }}"
                        data-categories="{{ json_encode($categories) }}"
                        data-colors="{{ json_encode($chartColors) }}" {{-- Add this line --}}
                        class="hidden">
                    </div>

                    <div id="attendanceChart"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- The script is updated to handle the new data structure --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chartDataElement = document.getElementById('chartData');

            if (chartDataElement) {
                const seriesData = JSON.parse(chartDataElement.dataset.series);
                const categoriesData = JSON.parse(chartDataElement.dataset.categories);
                const colorsData = JSON.parse(chartDataElement.dataset.colors); // Read the colors

                const options = {
                    series: seriesData,
                    colors: colorsData, // Use the generated colors for the chart
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
                            // This is the key setting that applies a different color to each bar
                            distributed: true, 
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
                        categories: categoriesData,
                    },
                    tooltip: {
                        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                        x: {
                            format: 'HH:mm'
                        }
                    },
                    legend: {
                        // We hide the legend as colors are now self-explanatory per employee
                        show: false 
                    },
                    grid: {
                        borderColor: '#90A4AE',
                        strokeDashArray: 2,
                    }
                };
                
                if (seriesData.length === 0 || seriesData[0].data.length === 0) {
                    document.querySelector("#attendanceChart").innerHTML = `<div class="text-center p-10">{{ __("No attendance records found for today.") }}</div>`;
                } else {
                    const chart = new ApexCharts(document.querySelector("#attendanceChart"), options);
                    chart.render();
                }
            }
        });
    </script>
</x-app-layout>