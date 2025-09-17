<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Yearly Attendance Report for') }}: {{ $employee->full_name }}
            </h2>

            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                @php
                $prevYear = $targetDate->copy()->subYear();
                $nextYear = $targetDate->copy()->addYear();
                @endphp

                <a href="{{ route('employees.reports.yearly', ['employee' => $employee->id, 'year' => $prevYear->year]) }}"
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    &lt; {{ __('Previous Year') }}
                </a>

                <span class="font-bold text-lg text-gray-800 dark:text-gray-200">
                    {{ $targetDate->year }}
                </span>

                <a href="{{ route('employees.reports.yearly', ['employee' => $employee->id, 'year' => $nextYear->year]) }}"
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    {{ __('Next Year') }} &gt;
                </a>
            </div>
        </div>
    </x-slot>

    {{-- --- NEW: CSS for clickable labels --- --}}
    <style>
        .apexcharts-xaxis-label {
            cursor: pointer;
        }
    </style>

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
            const seriesData = JSON.parse(chartElement.dataset.series);
            const categoriesData = JSON.parse(chartElement.dataset.categories);

            const options = {
                series: seriesData,
                chart: {
                    type: 'bar',
                    height: 400,
                    fontFamily: 'inherit',
                    toolbar: {
                        show: true
                    },
                    // --- NEW: Event listener for X-axis clicks ---
                    events: {
                        xAxisLabelClick: function(event, chartContext, config) {
                            // The index of the clicked month (0 for January, 1 for February, etc.)
                            const monthIndex = config.labelIndex;
                            // The month number (1 for January, 2 for February, etc.)
                            const monthNumber = monthIndex + 1;

                            // We create a URL template using Blade, with a placeholder for the month
                            let urlTemplate = "{{ route('employees.reports.monthly', ['employee' => $employee->id, 'year' => $targetDate->year, 'month' => '__MONTH__']) }}";

                            // Replace the placeholder with the actual month number
                            let finalUrl = urlTemplate.replace('__MONTH__', monthNumber);

                            // Redirect the user to the monthly report page
                            window.location.href = finalUrl;
                        }
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                    },
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        return val > 0 ? val.toFixed(1) + "h" : "";
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    categories: categoriesData,
                },
                yaxis: {
                    title: {
                        text: 'Hours'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return val.toFixed(2) + " hours"
                        }
                    }
                }
            };

            const chart = new ApexCharts(chartElement, options);
            chart.render();
        });
    </script>
    @endpush
</x-app-layout>