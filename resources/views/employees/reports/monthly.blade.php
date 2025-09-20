<x-app-layout>
    <x-slot name="header">
        {{-- We use a flex container to align title and navigation --}}
        <div class="flex justify-between items-center">

            {{-- Left Side: Title --}}
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Monthly Attendance Report for') }}: {{ $employee->full_name }}
            </h2>

            {{-- Right Side: Month Navigation --}}
            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                @php
                $prevMonth = $targetDate->copy()->subMonth();
                $nextMonth = $targetDate->copy()->addMonth();
                @endphp

                {{-- Previous Month Link --}}
                <a href="{{ route('employees.reports.monthly', ['employee' => $employee->id, 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    &lt; {{ __('Previous Month') }}
                </a>

                {{-- Current Month Display (Using Jalali for friendly display) --}}
                <span class="font-bold text-lg text-gray-800 dark:text-gray-200">
                    {{ Morilog\Jalali\Jalalian::fromCarbon($targetDate)->format('%B %Y') }}
                </span>

                {{-- Next Month Link --}}
                <a href="{{ route('employees.reports.monthly', ['employee' => $employee->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
                    class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    {{ __('Next Month') }} &gt;
                </a>
            </div>
        </div>
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
                        height: 600,
                        type: 'rangeBar',
                        toolbar: {
                            show: true,
                            autoSelected: 'pan'
                        }
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            barHeight: 6,
                        }
                    },
                    colors: ['#000000', '#28a745', '#dc3545'],
                    xaxis: {
                        type: 'datetime',
                        // --- FINAL FIX FOR X-AXIS RANGE ---
                        min: new Date('1970-01-01T00:00:00.000Z').getTime(),
                        max: new Date('1970-01-01T23:59:59.000Z').getTime(),
                        tickAmount: 12, // Suggest 12 ticks (e.g., every 2 hours)
                        labels: {
                            formatter: function(val) {
                                // Use English locale for labels to avoid RTL issues
                                return new Date(val).toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit',
                                    hour12: false
                                });
                            }
                        }
                    },
                    yaxis: {
                        categories: categoriesData,
                    },
                    legend: {
                        position: 'top',
                    },
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