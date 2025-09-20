<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Monthly Attendance Report for') }}: {{ $employee->full_name }}
            </h2>
            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                @php
                    $prevMonth = $targetDate->copy()->subMonth();
                    $nextMonth = $targetDate->copy()->addMonth();
                @endphp
                <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
                   class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    &lt; {{ __('Previous Month') }}
                </a>
                <span class="font-bold text-lg text-gray-800 dark:text-gray-200">
                    {{ Morilog\Jalali\Jalalian::fromCarbon($targetDate)->format('%B %Y') }}
                </span>
                <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
                   class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                    {{ __('Next Month') }} &gt;
                </a>
            </div>
        </div>
    </x-slot>

    <style>
        .d3-chart-container { font-family: Tahoma, sans-serif; background: #f7f7f7; padding: 10px; display: flex; flex-direction: column; align-items: center; }
        .d3-chart-container svg { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); display: block; }
        .interval-line { stroke-width: 3px; stroke-linecap: butt; }
        .grid line { stroke: #e0e0e0; stroke-opacity: 0.8; shape-rendering: crispEdges; }
        .grid path { stroke-width: 0; }
        .axis path, .axis line { stroke: #999; }
        .tooltip { position: absolute; background: rgba(50,50,50,0.9); color: #fff; padding: 6px 12px; border-radius: 6px; font-size: 13px; pointer-events: none; opacity: 0; direction: ltr; }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="d3-chart-container">
                        <svg id="chart"></svg>
                    </div>
                    <div class="tooltip" id="tooltip"></div>
                    <div id="chart-data" class="hidden"
                         data-events='@json($d3ChartData)'
                         data-days-in-month="{{ $targetDate->daysInMonth }}">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartDataElement = document.getElementById('chart-data');
        const eventData = JSON.parse(chartDataElement.dataset.events);
        const daysInMonth = parseInt(chartDataElement.dataset.daysInMonth);

        const tooltip = d3.select("#tooltip");
        const svg = d3.select("#chart");
        const margin = {top: 20, right: 20, bottom: 40, left: 40};

        function updateChart() {
            // --- THIS IS THE FIX: Use 'chartDataElement' instead of 'chartElement' ---
            const width = chartDataElement.parentElement.clientWidth * 0.95;
            const height = width * 2 / 4; 
            svg.attr("width", width).attr("height", height);
            const chartWidth = width - margin.left - margin.right;
            const chartHeight = height - margin.top - margin.bottom;

            svg.selectAll("*").remove();
            const g = svg.append("g").attr("transform", `translate(${margin.left},${margin.top})`);

            const x = d3.scaleLinear().domain([6, 22]).range([0, chartWidth]);
            const y = d3.scaleBand().domain(d3.range(1, daysInMonth + 1)).range([0, chartHeight]).padding(0.02);

            g.append("g").attr("class","grid").call(d3.axisBottom(x).ticks(16).tickSize(-chartHeight).tickFormat("")).attr("transform",`translate(0,${chartHeight})`);
            g.append("g").attr("class","grid").call(d3.axisLeft(y).tickSize(-chartWidth).tickFormat(""));
            g.append("g").attr("class","axis x-axis").attr("transform",`translate(0,${chartHeight})`).call(d3.axisBottom(x).ticks(16).tickFormat(d => d + ":00"));
            g.append("g").attr("class","axis y-axis").call(d3.axisLeft(y));

            const fullData = Array.from({length: daysInMonth}, (_, i) => {
                const day = i + 1;
                const found = eventData.find(e => e.day === day);
                return found ? found : { day: day, intervals: [] };
            });

            fullData.forEach(d => {
                d.intervals.forEach(interval => {
                    const color = "#1f77b4";

                    g.append("line")
                     .attr("class", "interval-line")
                     .attr("x1", x(interval.start))
                     .attr("x2", x(interval.end))
                     .attr("y1", y(d.day) + y.bandwidth() / 2)
                     .attr("y2", y(d.day) + y.bandwidth() / 2)
                     .attr("stroke", color)
                     .on("mouseover", event => {
                       tooltip.style("opacity", 1)
                              .html(`روز ${d.day}<br>ساعت: ${interval.start.toFixed(2)} - ${interval.end.toFixed(2)}`)
                              .style("left", (event.pageX + 12) + "px")
                              .style("top", (event.pageY - 25) + "px");
                     })
                     .on("mousemove", event => {
                       tooltip.style("left", (event.pageX + 12) + "px").style("top", (event.pageY - 25) + "px");
                     })
                     .on("mouseout", () => tooltip.style("opacity", 0));

                    [interval.start, interval.end].forEach(cx => {
                        g.append("circle")
                         .attr("cx", x(cx))
                         .attr("cy", y(d.day) + y.bandwidth() / 2)
                         .attr("r", 2.5)
                         .attr("fill", color);
                    });
                });
            });
        }
        
        updateChart();
        window.addEventListener("resize", updateChart);
    });
    </script>
    @endpush
</x-app-layout>