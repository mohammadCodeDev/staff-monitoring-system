<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Monthly Attendance Report for') }}: {{ $employee->full_name }}
            </h2>
            <a href="{{ route('employees.reports.yearly', ['employee' => $employee->id, 'year' => $targetDate->year]) }}"
                class="px-4 py-2 text-xs font-semibold text-white uppercase bg-gray-600 rounded-md hover:bg-gray-500 transition">
                {{ __('View Yearly Report') }}
            </a>
        </div>
    </x-slot>

    <style>
        .axis.y-axis text {
            transform: translateX(-10px);
        }

        .d3-chart-container {
            font-family: Tahoma, sans-serif;
            background: #f7f7f7;
            padding: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .d3-chart-container svg {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: block;
        }

        .interval-line {
            stroke-width: 3px;
            stroke-linecap: butt;
        }

        .grid line {
            stroke: #e0e0e0;
            stroke-opacity: 0.8;
            shape-rendering: crispEdges;
        }

        .grid path {
            stroke-width: 0;
        }

        .axis path,
        .axis line {
            stroke: #999;
        }

        .tooltip {
            position: absolute;
            background: rgba(50, 50, 50, 0.9);
            color: #fff;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            pointer-events: none;
            opacity: 0;
            direction: ltr;
        }
    </style>

    <div class="py-12" x-data="{ officeHoursChecked: false, amPmChecked: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-4 flex justify-center items-center space-x-6 rtl:space-x-reverse">
                    @php
                    $prevMonth = $targetDate->copy()->subMonth();
                    $nextMonth = $targetDate->copy()->addMonth();
                    $prevYear = $targetDate->copy()->subYear();
                    $nextYear = $targetDate->copy()->addYear();
                    @endphp
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $prevYear->year, 'month' => $prevYear->month]) }}" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">&lt;</a>
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open" class="font-bold text-lg text-gray-800 dark:text-gray-200 w-24 text-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md px-2">
                                {{ $useJalali ? Morilog\Jalali\Jalalian::fromCarbon($targetDate)->format('%Y') : $targetDate->year }}
                            </button>
                            <div x-show="open" x-transition class="absolute z-10 mt-2 w-32 bg-white dark:bg-gray-700 rounded-md shadow-lg max-h-60 overflow-auto">
                                @foreach ($yearRange as $year)
                                <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $year, 'month' => $targetDate->month]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ $useJalali ? Morilog\Jalali\Jalalian::fromCarbon(Carbon\Carbon::createFromDate($year, 1, 1))->format('%Y') : $year }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $nextYear->year, 'month' => $nextYear->month]) }}" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">&gt;</a>
                    </div>

                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">&lt;</a>
                        <div x-data="{ open: false }" @click.away="open = false" class="relative">
                            <button @click="open = !open" class="font-bold text-lg text-gray-800 dark:text-gray-200 w-32 text-center hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md px-2">
                                {{ $useJalali ? Morilog\Jalali\Jalalian::fromCarbon($targetDate)->format('%B') : $targetDate->format('F') }}
                            </button>
                            <div x-show="open" x-transition class="absolute z-10 mt-2 w-32 bg-white dark:bg-gray-700 rounded-md shadow-lg max-h-60 overflow-auto">
                                @foreach ($allMonths as $monthNumber => $monthName)
                                <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $targetDate->year, 'month' => $monthNumber]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ $monthName }}
                                </a>
                                @endforeach
                            </div>
                        </div>
                        <a href="{{ route('employees.reports.monthly_d3', ['employee' => $employee->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}" class="px-3 py-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition">&gt;</a>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="d3-chart-container"><svg id="chart"></svg></div>
                    <div class="tooltip" id="tooltip"></div>
                    <div id="chart-data" class="hidden" data-events='@json($d3ChartData)' data-days-in-month="{{ $daysInMonth }}" data-office-hours='@json($officeHours)'></div>
                </div>
            </div>

            <div class="mt-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 flex flex-col items-center justify-center space-y-4">
                    <div class="flex items-center justify-center space-x-6 rtl:space-x-reverse">
                        <label class="flex items-center space-x-2 rtl:space-x-reverse text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" id="officeHoursToggle" x-model="officeHoursChecked" @change="if(officeHoursChecked) amPmChecked = false; updateChart();" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                            <span>تفکیک ساعات اداری</span>
                        </label>
                        <label class="flex items-center space-x-2 rtl:space-x-reverse text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" id="amPmToggle" x-model="amPmChecked" @change="if(amPmChecked) officeHoursChecked = false; updateChart();" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                            <span>تفکیک صبح و عصر</span>
                        </label>
                    </div>
                    <div x-show="officeHoursChecked" x-transition class="text-sm text-gray-600 dark:text-gray-400 flex space-x-4 rtl:space-x-reverse">
                        <span>مجموع ساعات اداری: <strong id="officeTotal" class="text-blue-600 dark:text-blue-400"></strong></span>
                        <span>مجموع ساعات غیراداری: <strong id="nonOfficeTotal" class="text-orange-600 dark:text-orange-400"></strong></span>
                    </div>
                    <div x-show="amPmChecked" x-transition class="text-sm text-gray-600 dark:text-gray-400 flex space-x-4 rtl:space-x-reverse">
                        <span>مجموع ساعات صبح: <strong id="amTotal" class="text-green-600 dark:text-green-400"></strong></span>
                        <span>مجموع ساعات عصر: <strong id="pmTotal" class="text-red-600 dark:text-red-400"></strong></span>
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
            const officeHours = JSON.parse(chartDataElement.dataset.officeHours);
            const officeHoursToggle = document.getElementById('officeHoursToggle');
            const amPmToggle = document.getElementById('amPmToggle');
            const tooltip = d3.select("#tooltip");
            const svg = d3.select("#chart");
            const margin = {
                top: 20,
                right: 20,
                bottom: 40,
                left: 40
            };

            function decimalToHHMM(decimal) {
                const hours = Math.floor(decimal);
                const minutes = Math.round((decimal - hours) * 60);
                return hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0');
            }

            function segmentInterval(interval, coloringMode) {
                const segments = [];
                let {
                    start,
                    end
                } = interval;
                if (coloringMode === 'none' || !coloringMode) {
                    return [{
                        s: start,
                        e: end,
                        color: "#000000"
                    }];
                }
                const boundaries = (coloringMode === 'office') ? [officeHours.start, officeHours.end] : [12];
                let current = start;
                boundaries.forEach(boundary => {
                    if (current < boundary && end > boundary) {
                        segments.push({
                            s: current,
                            e: boundary
                        });
                        current = boundary;
                    }
                });
                segments.push({
                    s: current,
                    e: end
                });
                return segments.map(seg => {
                    let color;
                    const midPoint = (seg.s + seg.e) / 2;
                    if (coloringMode === 'office') {
                        color = (midPoint >= officeHours.start && midPoint < officeHours.end) ? '#1f77b4' : '#ff7f0e';
                    } else {
                        color = (midPoint < 12) ? '#2ca02c' : '#d62728';
                    }
                    return {
                        ...seg,
                        color
                    };
                });
            }

            function calculateTotals(eventData) {
                let totals = {
                    office: 0,
                    nonOffice: 0,
                    am: 0,
                    pm: 0
                };
                const amPmBoundary = 12;
                eventData.forEach(day => {
                    if (day.intervals) {
                        day.intervals.forEach(interval => {
                            const totalDuration = interval.end - interval.start;
                            const overlapStart = Math.max(interval.start, officeHours.start);
                            const overlapEnd = Math.min(interval.end, officeHours.end);
                            const officeDuration = Math.max(0, overlapEnd - overlapStart);
                            totals.office += officeDuration;
                            totals.nonOffice += (totalDuration - officeDuration);
                            const amOverlapEnd = Math.min(interval.end, amPmBoundary);
                            const amDuration = Math.max(0, amOverlapEnd - interval.start);
                            totals.am += amDuration;
                            totals.pm += (totalDuration - amDuration);
                        });
                    }
                });
                return totals;
            }

            function updateChart() {
                const officeChecked = officeHoursToggle.checked;
                const amPmChecked = amPmToggle.checked;
                let coloringMode = 'none';
                if (officeChecked) coloringMode = 'office';
                else if (amPmChecked) coloringMode = 'ampm';
                if (coloringMode !== 'none') {
                    const totals = calculateTotals(eventData);
                    document.getElementById('officeTotal').textContent = decimalToHHMM(totals.office);
                    document.getElementById('nonOfficeTotal').textContent = decimalToHHMM(totals.nonOffice);
                    document.getElementById('amTotal').textContent = decimalToHHMM(totals.am);
                    document.getElementById('pmTotal').textContent = decimalToHHMM(totals.pm);
                }
                const width = chartDataElement.parentElement.clientWidth * 0.95;
                const height = 500;
                svg.attr("width", width).attr("height", height);
                const chartWidth = width - margin.left - margin.right;
                const chartHeight = height - margin.top - margin.bottom;
                svg.selectAll("*").remove();
                const g = svg.append("g").attr("transform", `translate(${margin.left},${margin.top})`);
                const x = d3.scaleLinear().domain([6, 22]).range([0, chartWidth]);
                const y = d3.scaleBand().domain(d3.range(1, daysInMonth + 1)).range([0, chartHeight]).padding(0.4);
                g.append("g").attr("class", "grid").call(d3.axisBottom(x).ticks(16).tickSize(-chartHeight).tickFormat("")).attr("transform", `translate(0,${chartHeight})`);
                g.append("g").attr("class", "grid").call(d3.axisLeft(y).tickSize(-chartWidth).tickFormat(""));
                g.append("g").attr("class", "axis x-axis").attr("transform", `translate(0,${chartHeight})`).call(d3.axisBottom(x).ticks(16).tickFormat(d => d.toFixed(0) + ":00"));
                g.append("g").attr("class", "axis y-axis").call(d3.axisLeft(y));
                const fullData = Array.from({
                    length: daysInMonth
                }, (_, i) => {
                    const day = i + 1;
                    const found = eventData.find(e => e.day === day);
                    return found ? found : {
                        day: day,
                        intervals: [],
                        entries: [],
                        exits: []
                    };
                });
                fullData.forEach(d => {
                    if (d.intervals) {
                        d.intervals.forEach(interval => {
                            const segments = segmentInterval(interval, coloringMode);
                            segments.forEach(seg => {
                                g.append("line")
                                    .attr("class", "interval-line").attr("stroke", seg.color)
                                    .attr("x1", x(seg.s)).attr("x2", x(seg.e))
                                    .attr("y1", y(d.day) + y.bandwidth() / 2).attr("y2", y(d.day) + y.bandwidth() / 2)
                                    .on("mouseover", event => {
                                        tooltip.style("opacity", 1)
                                            .html(`روز ${d.day} (ساعات کاری)<br>ساعت: ${decimalToHHMM(interval.start)} - ${decimalToHHMM(interval.end)}`)
                                            .style("left", (event.pageX + 12) + "px").style("top", (event.pageY - 25) + "px");
                                    })
                                    .on("mouseout", () => tooltip.style("opacity", 0));
                            });
                        });
                    }
                    if (d.entries) {
                        d.entries.forEach(entryHour => {
                            g.append("circle")
                                .attr("cx", x(entryHour)).attr("cy", y(d.day) + y.bandwidth() / 2)
                                .attr("r", 4).attr("fill", "#28a745")
                                .on("mouseover", event => {
                                    tooltip.style("opacity", 1)
                                        .html(`روز ${d.day} (ورود)<br>ساعت: ${decimalToHHMM(entryHour)}`)
                                        .style("left", (event.pageX + 12) + "px").style("top", (event.pageY - 25) + "px");
                                })
                                .on("mouseout", () => tooltip.style("opacity", 0));
                        });
                    }
                    if (d.exits) {
                        d.exits.forEach(exitHour => {
                            g.append("circle")
                                .attr("cx", x(exitHour)).attr("cy", y(d.day) + y.bandwidth() / 2)
                                .attr("r", 4).attr("fill", "#dc3545")
                                .on("mouseover", event => {
                                    tooltip.style("opacity", 1)
                                        .html(`روز ${d.day} (خروج)<br>ساعت: ${decimalToHHMM(exitHour)}`)
                                        .style("left", (event.pageX + 12) + "px").style("top", (event.pageY - 25) + "px");
                                })
                                .on("mouseout", () => tooltip.style("opacity", 0));
                        });
                    }
                });
            }
            officeHoursToggle.addEventListener('change', () => {
                if (officeHoursToggle.checked) amPmToggle.checked = false;
                updateChart();
            });
            amPmToggle.addEventListener('change', () => {
                if (amPmToggle.checked) officeHoursToggle.checked = false;
                updateChart();
            });
            updateChart();
            window.addEventListener("resize", updateChart);
        });
    </script>
    @endpush
</x-app-layout>