<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $dashboard->title }} - Dynamic Dashboard</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-gray-50">
        <div class="max-w-6xl mx-auto py-8 px-4">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Dashboard Header -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $dashboard->title }}</h1>
                        @if($dashboard->description)
                            <p class="mt-2 text-gray-600">{{ $dashboard->description }}</p>
                        @endif
                        <p class="mt-2 text-sm text-gray-500">
                            Created {{ $dashboard->created_at->format('M j, Y \a\t g:i A') }}
                        </p>
                    </div>
                    <div>
                        <button onclick="openAddChartModal()" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Chart
                        </button>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-6 gap-4">
                    <h2 class="text-xl font-semibold text-gray-900">Charts</h2>
                </div>

                <!-- Charts List (flex for resizable width) -->
                <div id="charts-container" class="flex flex-wrap gap-6 max-h-[70vh] overflow-y-auto pr-1">
                    @if(!empty($chartConfigs ?? []) && count($chartConfigs))
                        @foreach($chartConfigs as $cfg)
                            <div class="chart-card border rounded-lg p-4 bg-white shadow w-[calc(50%-0.75rem)]" data-detail-id="{{ $cfg['id'] }}" data-width="{{ $cfg['widthPx'] ?? '' }}" data-height="{{ $cfg['heightPx'] ?? '' }}">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="drag-handle cursor-move text-gray-400 hover:text-gray-600" title="Drag to reorder" aria-label="Drag to reorder">
                                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                <path d="M7 4a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zM7 8a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zM7 12a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2zM7 16a1 1 0 100-2 1 1 0 000 2zm6 0a1 1 0 100-2 1 1 0 000 2z"/>
                                            </svg>
                                        </button>
                                        <h3 class="font-medium text-gray-900">{{ ucfirst($cfg['moduleName']) }} - {{ $cfg['title'] }}</h3>
                                    </div>
                                    <div class="flex items-center gap-3"><div class="flex items-center gap-1">
                                            <button type="button" onclick="openEditChartModal({{ $cfg['id'] }}, {{ $cfg['chartId'] }}, '{{ $cfg['moduleName'] }}', '{{ $cfg['xLabel'] }}', '{{ $cfg['yLabel'] }}', '{{ $cfg['dateRange'] ?? '' }}', '{{ $cfg['amountMinRange'] ?? '' }}', '{{ $cfg['amountMaxRange'] ?? '' }}')" 
                           
                           
                           
                                            class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 p-1 text-xs font-medium text-blue-700 hover:bg-blue-100 hover:border-blue-300" 
                                                    title="Edit chart">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                            <form method="POST" action="{{ route('dynamic-dashboard.charts.destroy', [$dashboard, $cfg['id']]) }}" onsubmit="return confirm('Delete this chart from the dashboard?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center rounded-md border border-red-200 bg-red-50 p-1 text-xs font-medium text-red-700 hover:bg-red-100 hover:border-red-300">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2m-9 0h10" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 border rounded-md bg-white shadow-sm p-3">
                                    <form class="per-chart-filter flex flex-nowrap items-end gap-3 overflow-x-auto" data-detail-id="{{ $cfg['id'] }}" data-module="{{ $cfg['moduleName'] }}">
                                    <div class="shrink-0">
                                        <label class="block text-xs font-medium text-gray-700">Date Range</label>
                                        <select name="date_range" class="date-range-select mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            <option value="" {{ empty($cfg['dateRange']) ? 'selected' : '' }}>Custom Range</option>
                                            <option value="last_7_days" {{ $cfg['dateRange'] === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                                            <option value="this_week" {{ $cfg['dateRange'] === 'this_week' ? 'selected' : '' }}>This Week</option>
                                            <option value="last_15_days" {{ $cfg['dateRange'] === 'last_15_days' ? 'selected' : '' }}>Last 15 Days</option>
                                            <option value="this_month" {{ $cfg['dateRange'] === 'this_month' ? 'selected' : '' }}>This Month</option>
                                            <option value="last_month" {{ $cfg['dateRange'] === 'last_month' ? 'selected' : '' }}>Last Month</option>
                                            <option value="this_year" {{ $cfg['dateRange'] === 'this_year' ? 'selected' : '' }}>This Year</option>
                                        </select>
                                    </div>
                                    <div class="shrink-0">
                                        <label class="block text-xs font-medium text-gray-700">From</label>
                                        <input type="date" name="from" class="mt-1 block w-25 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-2 py-1" />
                                    </div>
                                    <div class="shrink-0">
                                        <label class="block text-xs font-medium text-gray-700">To</label>
                                        <input type="date" name="to" class="mt-1 block w-25 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-2 py-1" />
                                    </div>
                                    <div class="shrink-0 flex gap-2">
                                            <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-emerald-600 text-white hover:bg-emerald-700">Filter</button>
                                        <button type="button" class="per-chart-clear inline-flex items-center px-3 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Clear</button>
                                    </div>
                                    </form>
                                </div>
                                <div class="relative group chart-resizable" data-chart-id="{{ $cfg['id'] }}" style="height: {{ isset($cfg['heightPx']) && $cfg['heightPx'] ? $cfg['heightPx'].'px' : '16rem' }};">
                                    <div id="chart-{{ $cfg['id'] }}" style="width: 100%; height: 100%;"></div>
                                    <button type="button" class="absolute bottom-1 right-1 w-4 h-4 rounded bg-gray-200 text-gray-500 hover:bg-gray-300 flex items-center justify-center cursor-se-resize shadow-sm border border-gray-300 resize-handle" title="Resize">
                                        <svg viewBox="0 0 20 20" class="w-3 h-3" fill="currentColor" aria-hidden="true">
                                            <path d="M5 15h2v2H5v-2zm4-4h2v2H9v-2zm4-4h2v2h-2V7z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Empty state when no charts -->
                        <div class="w-full text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No charts yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by adding your first chart.</p>
                            <div class="mt-6">
                                <button onclick="openAddChartModal()" 
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Add Your First Chart
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Add Chart Modal -->
        <div id="addChartModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
            <div class="bg-white w-full max-w-lg rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold">Add Chart</h3>
                </div>
                <form id="addChartForm" action="{{ route('dynamic-dashboard.charts.store', $dashboard) }}" method="POST" class="px-6 py-5 space-y-4">
                    @csrf

                    <div>
                        <label for="chart_id" class="block text-sm font-medium text-gray-700">Chart Name</label>
                        <select id="chart_id" name="chart_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select chart</option>
                            @foreach($charts as $chart)
                                <option value="{{ $chart->id }}">{{ $chart->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="module_name" class="block text-sm font-medium text-gray-700">Module Name</label>
                        <select id="module_name" name="module_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select module</option>
                            <option value="products">Products</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="x_axis" class="block text-sm font-medium text-gray-700">X Axis</label>
                            <select id="x_axis" name="x_axis" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select field</option>
                            </select>
                        </div>
                        <div>
                            <label for="y_axis" class="block text-sm font-medium text-gray-700">Y Axis</label>
                            <select id="y_axis" name="y_axis" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select field</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="date_range" class="block text-sm font-medium text-gray-700">Default Date Range</label>
                        <select id="date_range" name="date_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Time</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="this_week">This Week</option>
                            <option value="last_15_days">Last 15 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="amount_min_range" class="block text-sm font-medium text-gray-700">Amount Min Range</label>
                            <input type="number" id="amount_min_range" name="amount_min_range" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00">
                        </div>
                        <div>
                            <label for="amount_max_range" class="block text-sm font-medium text-gray-700">Amount Max Range</label>
                            <input type="number" id="amount_max_range" name="amount_max_range" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="closeAddChartModal()" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Chart Modal -->
        <div id="editChartModal" class="fixed inset-0 hidden items-center justify-center bg-black/40 z-50">
            <div class="bg-white w-full max-w-lg rounded-lg shadow-lg">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold">Edit Chart</h3>
                </div>
                <form id="editChartForm" method="POST" class="px-6 py-5 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="edit_chart_id" class="block text-sm font-medium text-gray-700">Chart Name</label>
                        <select id="edit_chart_id" name="chart_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select chart</option>
                            @foreach($charts as $chart)
                                <option value="{{ $chart->id }}">{{ $chart->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_module_name" class="block text-sm font-medium text-gray-700">Module Name</label>
                        <select id="edit_module_name" name="module_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Select module</option>
                            <option value="products">Products</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_x_axis" class="block text-sm font-medium text-gray-700">X Axis</label>
                            <select id="edit_x_axis" name="x_axis" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select field</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_y_axis" class="block text-sm font-medium text-gray-700">Y Axis</label>
                            <select id="edit_y_axis" name="y_axis" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select field</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label for="edit_date_range" class="block text-sm font-medium text-gray-700">Default Date Range</label>
                        <select id="edit_date_range" name="date_range" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">All Time</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="this_week">This Week</option>
                            <option value="last_15_days">Last 15 Days</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_amount_min_range" class="block text-sm font-medium text-gray-700">Amount Min Range</label>
                            <input type="number" id="edit_amount_min_range" name="amount_min_range" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00">
                        </div>
                        <div>
                            <label for="edit_amount_max_range" class="block text-sm font-medium text-gray-700">Amount Max Range</label>
                            <input type="number" id="edit_amount_max_range" name="amount_max_range" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" placeholder="0.00">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="closeEditChartModal()" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Update</button>
                    </div>
                </form>
            </div>
        </div>

        @if(!empty($chartConfigs ?? []) && count($chartConfigs))
            <script src="https://cdn.jsdelivr.net/npm/ag-charts-enterprise/dist/umd/ag-charts-enterprise.min.js"></script>
        @endif

        <script>
            const addChartModal = document.getElementById('addChartModal');
            const moduleSelect = document.getElementById('module_name');
            const xAxisSelect = document.getElementById('x_axis');
            const yAxisSelect = document.getElementById('y_axis');

            function openAddChartModal() {
                addChartModal.classList.remove('hidden');
                addChartModal.classList.add('flex');
            }

            function closeAddChartModal() {
                addChartModal.classList.add('hidden');
                addChartModal.classList.remove('flex');
            }

            // Edit Chart Modal functions
            const editChartModal = document.getElementById('editChartModal');
            const editChartForm = document.getElementById('editChartForm');
            const editModuleSelect = document.getElementById('edit_module_name');
            const editXAxisSelect = document.getElementById('edit_x_axis');
            const editYAxisSelect = document.getElementById('edit_y_axis');
            const editChartIdSelect = document.getElementById('edit_chart_id');

            function openEditChartModal(chartDetailId, chartId, moduleName, xLabel, yLabel, dateRange, amountMinRange, amountMaxRange) {
                // Set the form action
                editChartForm.action = '{{ route('dynamic-dashboard.charts.update', [$dashboard, ':chartDetailId']) }}'.replace(':chartDetailId', chartDetailId);
                
                // Set current values
                editChartIdSelect.value = chartId || '';
                editModuleSelect.value = moduleName || 'products';
                document.getElementById('edit_date_range').value = dateRange || '';
                document.getElementById('edit_amount_min_range').value = amountMinRange || '';
                document.getElementById('edit_amount_max_range').value = amountMaxRange || '';
                
                // Store the axis values to set later
                window.pendingAxisValues = { xLabel: xLabel || '', yLabel: yLabel || '' };
                
                // Trigger module change to populate axis fields
                editModuleSelect.dispatchEvent(new Event('change'));
                
                // Also try to set axis values after a delay in case the change event doesn't fire
                setTimeout(() => {
                    if (window.pendingAxisValues && editXAxisSelect.options.length > 1) {
                        editXAxisSelect.value = window.pendingAxisValues.xLabel;
                        editYAxisSelect.value = window.pendingAxisValues.yLabel;
                        window.pendingAxisValues = null;
                    }
                }, 300);
                
                editChartModal.classList.remove('hidden');
                editChartModal.classList.add('flex');
            }

            function closeEditChartModal() {
                editChartModal.classList.add('hidden');
                editChartModal.classList.remove('flex');
                // Clear any pending values
                window.pendingAxisValues = null;
            }


            // Click outside to close
            addChartModal.addEventListener('click', function (e) {
                if (e.target === addChartModal) {
                    closeAddChartModal();
                }
            });

            editChartModal.addEventListener('click', function (e) {
                if (e.target === editChartModal) {
                    closeEditChartModal();
                }
            });

            // Populate x/y axis when module changes
            moduleSelect && moduleSelect.addEventListener('change', async function () {
                const module = this.value;
                // reset
                xAxisSelect.innerHTML = '<option value="">Select field</option>';
                yAxisSelect.innerHTML = '<option value="">Select field</option>';
                if (!module) return;
                try {
                    const params = new URLSearchParams({ module });
                    const res = await fetch('{{ route('dynamic-dashboard.module-fields') }}' + '?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    (data.numeric || []).forEach(function (field) {
                        const opt = document.createElement('option');
                        opt.value = field;
                        opt.textContent = field;
                        xAxisSelect.appendChild(opt);
                    });
                    (data.string || []).forEach(function (field) {
                        const opt = document.createElement('option');
                        opt.value = field;
                        opt.textContent = field;
                        yAxisSelect.appendChild(opt);
                    });
                } catch (err) {
                    console.error(err);
                }
            });

            // Populate x/y axis when module changes in edit modal
            editModuleSelect && editModuleSelect.addEventListener('change', async function () {
                const module = this.value;
                // reset
                editXAxisSelect.innerHTML = '<option value="">Select field</option>';
                editYAxisSelect.innerHTML = '<option value="">Select field</option>';
                if (!module) return;
                try {
                    const params = new URLSearchParams({ module });
                    const res = await fetch('{{ route('dynamic-dashboard.module-fields') }}' + '?' + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();
                    (data.numeric || []).forEach(function (field) {
                        const opt = document.createElement('option');
                        opt.value = field;
                        opt.textContent = field;
                        editXAxisSelect.appendChild(opt);
                    });
                    (data.string || []).forEach(function (field) {
                        const opt = document.createElement('option');
                        opt.value = field;
                        opt.textContent = field;
                        editYAxisSelect.appendChild(opt);
                    });
                    
                    // Set the pending axis values after options are populated
                    if (window.pendingAxisValues) {
                        editXAxisSelect.value = window.pendingAxisValues.xLabel;
                        editYAxisSelect.value = window.pendingAxisValues.yLabel;
                        // Clear the pending values
                        window.pendingAxisValues = null;
                    }
                } catch (err) {
                    console.error(err);
                }
            });
            // Render charts
            (function () {
                const cfgs = @json($chartConfigs ?? []);
                if (!Array.isArray(cfgs) || !cfgs.length || typeof agCharts === 'undefined') return;
                window.__chartsById = window.__chartsById || {};
                
                cfgs.forEach(function (cfg) {
                    const el = document.getElementById('chart-' + cfg.id);
                    if (!el) return;
                    
                    // Convert Chart.js data format to AG Charts format
                    const data = cfg.labels.map((label, index) => ({
                        category: label,
                        value: cfg.data[index] || 0
                    }));
                    
                    // Map chart types from Chart.js to AG Charts
                    let chartType = 'column';
                    switch (cfg.type) {
                        case 'bar':
                            chartType = 'bar';
                            break;
                        case 'line':
                            chartType = 'line';
                            break;
                        case 'pie':
                            chartType = 'pie';
                            break;
                        case 'doughnut':
                            chartType = 'donut';
                            break;
                        case 'scatter':
                            chartType = 'scatter';
                            break;
                        case 'polarArea':
                        case 'radar':
                            chartType = 'column'; // Fallback to column for unsupported types
                            break;
                        default:
                            chartType = 'column';
                    }
                    
                    // Create AG Charts configuration
                    const options = {
                        container: el,
                        title: {
                            text: cfg.title
                        },
                        data: data,
                        series: []
                    };
                    
                    // Configure series based on chart type
                    if (chartType === 'pie' || chartType === 'donut') {
                        options.series = [{
                            type: chartType,
                            angleKey: 'value',
                            calloutLabelKey: 'category',
                            sectorLabelKey: 'value',
                            fills: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'],
                            sectorLabel: {
                                enabled: true,
                                formatter: function(params) {
                                    return params.datum.value;
                                }
                            }
                        }];
                    } else if (chartType === 'scatter') {
                        options.series = [{
                            type: 'scatter',
                            xKey: 'category',
                            yKey: 'value',
                            fill: '#3b82f6',
                            stroke: '#1d4ed8'
                        }];
                        options.axes = [
                            {
                                type: 'category',
                                position: 'bottom',
                                title: { text: cfg.yLabel || 'Category' }
                            },
                            {
                                type: 'number',
                                position: 'left',
                                title: { text: cfg.xLabel || 'Value' }
                            }
                        ];
                    } else {
                        options.series = [{
                            type: chartType,
                            xKey: 'category',
                            yKey: 'value',
                            fill: '#3b82f6',
                            stroke: '#1d4ed8'
                        }];
                        options.axes = [
                            {
                                type: 'category',
                                position: 'bottom',
                                title: { text: cfg.yLabel || 'Category' }
                            },
                            {
                                type: 'number',
                                position: 'left',
                                title: { text: cfg.xLabel || 'Value' }
                            }
                        ];
                    }
                    
                    const chartInstance = agCharts.AgCharts.create(options);
                    window.__chartsById[cfg.id] = chartInstance;
                    
                    // Store chart configuration for resize operations
                    window.__chartConfigsById = window.__chartConfigsById || {};
                    window.__chartConfigsById[cfg.id] = {
                        data: data,
                        options: options,
                        type: chartType,
                        title: cfg.title,
                        xLabel: cfg.xLabel,
                        yLabel: cfg.yLabel
                    };
                    
                    console.log('Created AG Chart with ID:', cfg.id, 'Type:', chartType);
                });
            })();

            // Per-chart filtering
            (function () {
                const forms = document.querySelectorAll('.per-chart-filter');
                const moduleFieldsUrl = '{{ route('dynamic-dashboard.module-fields') }}';
                const dataUrlBase = '{{ route('dynamic-dashboard.data', $dashboard) }}';

                // Removed populateDateFields function as date field selector is no longer needed

                async function fetchAndRender(form) {
                    const detailId = form.getAttribute('data-detail-id');
                    const dateRange = form.querySelector('select[name="date_range"]').value;
                    const from = form.querySelector('input[name="from"]').value;
                    const to = form.querySelector('input[name="to"]').value;
                    const params = new URLSearchParams();
                    if (detailId) params.set('detail_id', detailId);
                    if (dateRange) params.set('date_range', dateRange);
                    if (from) params.set('from', from);
                    if (to) params.set('to', to);
                    try {
                        const res = await fetch(dataUrlBase + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const json = await res.json();
                        const cfg = (json.charts && json.charts.length) ? json.charts[0] : null;
                        if (!cfg) return;
                        var el = document.getElementById('chart-' + cfg.id);
                        if (!el) return;
                        window.__chartsById = window.__chartsById || {};
                        var existing = window.__chartsById[cfg.id];
                        if (existing) {
                            // Convert Chart.js data format to AG Charts format
                            const newData = cfg.labels.map((label, index) => ({
                                category: label,
                                value: cfg.data[index] || 0
                            }));
                            
                            // Map chart types from Chart.js to AG Charts
                            let chartType = 'column';
                            switch (cfg.type) {
                                case 'bar':
                                    chartType = 'bar';
                                    break;
                                case 'line':
                                    chartType = 'line';
                                    break;
                                case 'pie':
                                    chartType = 'pie';
                                    break;
                                case 'doughnut':
                                    chartType = 'donut';
                                    break;
                                case 'scatter':
                                    chartType = 'scatter';
                                    break;
                                case 'polarArea':
                                case 'radar':
                                    chartType = 'column'; // Fallback to column for unsupported types
                                    break;
                                default:
                                    chartType = 'column';
                            }
                            
                            // Create complete update configuration
                            const updateOptions = {
                                data: newData,
                                title: {
                                    text: cfg.title
                                },
                                series: []
                            };
                            
                            // Configure series based on chart type
                            if (chartType === 'pie' || chartType === 'donut') {
                                updateOptions.series = [{
                                    type: chartType,
                                    angleKey: 'value',
                                    calloutLabelKey: 'category',
                                    sectorLabelKey: 'value',
                                    fills: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#84cc16', '#f97316'],
                                    sectorLabel: {
                                        enabled: true,
                                        formatter: function(params) {
                                            return params.datum.value;
                                        }
                                    }
                                }];
                            } else if (chartType === 'scatter') {
                                updateOptions.series = [{
                                    type: 'scatter',
                                    xKey: 'category',
                                    yKey: 'value',
                                    fill: '#3b82f6',
                                    stroke: '#1d4ed8'
                                }];
                                updateOptions.axes = [
                                    {
                                        type: 'category',
                                        position: 'bottom',
                                        title: { text: cfg.yLabel || 'Category' }
                                    },
                                    {
                                        type: 'number',
                                        position: 'left',
                                        title: { text: cfg.xLabel || 'Value' }
                                    }
                                ];
                            } else {
                                updateOptions.series = [{
                                    type: chartType,
                                    xKey: 'category',
                                    yKey: 'value',
                                    fill: '#3b82f6',
                                    stroke: '#1d4ed8'
                                }];
                                updateOptions.axes = [
                                    {
                                        type: 'category',
                                        position: 'bottom',
                                        title: { text: cfg.yLabel || 'Category' }
                                    },
                                    {
                                        type: 'number',
                                        position: 'left',
                                        title: { text: cfg.xLabel || 'Value' }
                                    }
                                ];
                            }
                            
                            // Update the chart with complete configuration
                            console.log('Updating chart ID:', cfg.id, 'with data:', newData.length, 'items');
                            try {
                                existing.update(updateOptions);
                                console.log('Chart updated successfully');
                            } catch (updateError) {
                                console.warn('Chart update failed, recreating chart:', updateError);
                                // If update fails, destroy and recreate the chart
                                existing.destroy();
                                
                                // Recreate the chart with new configuration
                                const newOptions = {
                                    container: el,
                                    title: {
                                        text: cfg.title
                                    },
                                    data: newData,
                                    series: updateOptions.series
                                };
                                
                                if (updateOptions.axes) {
                                    newOptions.axes = updateOptions.axes;
                                }
                                
                                const newChartInstance = agCharts.AgCharts.create(newOptions);
                                window.__chartsById[cfg.id] = newChartInstance;
                            }
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }

                // Helper function to calculate date ranges on frontend (matches backend Carbon logic)
                function calculateDateRange(dateRange) {
                    const now = new Date();
                    
                    switch (dateRange) {
                        case 'last_7_days':
                            const sevenDaysAgo = new Date(now);
                            sevenDaysAgo.setDate(now.getDate() - 7);
                            sevenDaysAgo.setHours(0, 0, 0, 0); // start of day
                            const todayEnd = new Date(now);
                            todayEnd.setHours(23, 59, 59, 999); // end of day
                            return {
                                from: sevenDaysAgo.toISOString().split('T')[0],
                                to: todayEnd.toISOString().split('T')[0]
                            };
                        
                        case 'this_week':
                            const startOfWeek = new Date(now);
                            // JavaScript getDay() returns 0 for Sunday, but we want Monday as start
                            const dayOfWeek = startOfWeek.getDay();
                            const daysFromMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
                            startOfWeek.setDate(now.getDate() - daysFromMonday);
                            startOfWeek.setHours(0, 0, 0, 0);
                            
                            const endOfWeek = new Date(startOfWeek);
                            endOfWeek.setDate(startOfWeek.getDate() + 6);
                            endOfWeek.setHours(23, 59, 59, 999);
                            return {
                                from: startOfWeek.toISOString().split('T')[0],
                                to: endOfWeek.toISOString().split('T')[0]
                            };
                        
                        case 'last_15_days':
                            const fifteenDaysAgo = new Date(now);
                            fifteenDaysAgo.setDate(now.getDate() - 15);
                            fifteenDaysAgo.setHours(0, 0, 0, 0);
                            const todayEnd15 = new Date(now);
                            todayEnd15.setHours(23, 59, 59, 999);
                            return {
                                from: fifteenDaysAgo.toISOString().split('T')[0],
                                to: todayEnd15.toISOString().split('T')[0]
                            };
                        
                        case 'this_month':
                            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
                            startOfMonth.setHours(0, 0, 0, 0);
                            const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);
                            endOfMonth.setHours(23, 59, 59, 999);
                            return {
                                from: startOfMonth.toISOString().split('T')[0],
                                to: endOfMonth.toISOString().split('T')[0]
                            };
                        
                        case 'last_month':
                            const startOfLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
                            startOfLastMonth.setHours(0, 0, 0, 0);
                            const endOfLastMonth = new Date(now.getFullYear(), now.getMonth(), 0);
                            endOfLastMonth.setHours(23, 59, 59, 999);
                            return {
                                from: startOfLastMonth.toISOString().split('T')[0],
                                to: endOfLastMonth.toISOString().split('T')[0]
                            };
                        
                        case 'this_year':
                            const startOfYear = new Date(now.getFullYear(), 0, 1);
                            startOfYear.setHours(0, 0, 0, 0);
                            const endOfYear = new Date(now.getFullYear(), 11, 31);
                            endOfYear.setHours(23, 59, 59, 999);
                            return {
                                from: startOfYear.toISOString().split('T')[0],
                                to: endOfYear.toISOString().split('T')[0]
                            };
                        
                        default:
                            return { from: '', to: '' };
                    }
                }

                forms.forEach(function (form) {
                    
                    // Handle date range selection
                    const dateRangeSelect = form.querySelector('select[name="date_range"]');
                    const fromInput = form.querySelector('input[name="from"]');
                    const toInput = form.querySelector('input[name="to"]');
                    
                    // Set initial date range if a default is selected
                    if (dateRangeSelect && dateRangeSelect.value) {
                        const selectedRange = dateRangeSelect.value;
                        const dates = calculateDateRange(selectedRange);
                        fromInput.value = dates.from;
                        toInput.value = dates.to;
                    }
                    
                    if (dateRangeSelect) {
                        dateRangeSelect.addEventListener('change', function () {
                            const selectedRange = this.value;
                            if (selectedRange) {
                                const dates = calculateDateRange(selectedRange);
                                fromInput.value = dates.from;
                                toInput.value = dates.to;
                                // Automatically trigger the filter
                                fetchAndRender(form);
                            } else {
                                // Clear dates when "Custom Range" is selected
                                fromInput.value = '';
                                toInput.value = '';
                            }
                        });
                    }
                    
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        fetchAndRender(form);
                    });
                    
                    const clearBtn = form.querySelector('.per-chart-clear');
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function () {
                            form.querySelector('input[name="from"]').value = '';
                            form.querySelector('input[name="to"]').value = '';
                            form.querySelector('select[name="date_range"]').value = '';
                            fetchAndRender(form);
                        });
                    }
                });
            })();
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            (function () {
                var container = document.getElementById('charts-container');
                if (!container || typeof Sortable === 'undefined') return;
                var sortable = new Sortable(container, {
                    animation: 150,
                    handle: '.drag-handle',
                    ghostClass: 'bg-blue-50',
                    dragClass: 'opacity-60',
                    forceFallback: false,
                    bubbleScroll: true,
                    scroll: true,
                    scrollSensitivity: 30,
                    scrollSpeed: 10,
                    onEnd: function (evt) {
                        // Get the new order of chart IDs
                        var chartCards = container.querySelectorAll('.chart-card');
                        var chartIds = [];
                        chartCards.forEach(function (card) {
                            var detailId = card.getAttribute('data-detail-id');
                            if (detailId) {
                                chartIds.push(parseInt(detailId, 10));
                            }
                        });
                        
                        // Send the new order to the server
                        if (chartIds.length > 0) {
                            saveChartOrder(chartIds);
                        }
                    }
                });
                window.__chartsSortable = sortable;

                // Function to save chart order to the server
                function saveChartOrder(chartIds) {
                    try {
                        var url = '{{ route('dynamic-dashboard.charts.order', $dashboard) }}';
                        var formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        chartIds.forEach(function (id, index) {
                            formData.append('chart_ids[' + index + ']', id);
                        });
                        
                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        })
                        .then(function (response) {
                            if (!response.ok) {
                                console.error('Failed to save chart order:', response.statusText);
                            }
                            return response.json();
                        })
                        .then(function (data) {
                            console.log('Chart order saved successfully:', data.message);
                        })
                        .catch(function (error) {
                            console.error('Error saving chart order:', error);
                        });
                    } catch (e) {
                        console.error('Error in saveChartOrder:', e);
                    }
                }

                // Enable resizing of chart cards
                function initResizableCharts() {
                    var resizableCards = container.querySelectorAll('.chart-resizable');
                    resizableCards.forEach(function (card) {
                        var handle = card.querySelector('.resize-handle');
                        if (!handle) return;
                        var startX = 0;
                        var startY = 0;
                        var startWidth = 0;
                        var startHeight = 0;
                        var cardWrapper = card.closest('.chart-card');
                        var dashboardMinHeight = 160; // px
                        var dashboardMinWidth = 240; // px
                        var dashboardMaxWidth = container.clientWidth - 24; // leave some gap

                        // Apply server-provided size if available
                        var wrapper = card.closest('.chart-card');
                        if (wrapper) {
                            var wAttr = wrapper.getAttribute('data-width');
                            var hAttr = wrapper.getAttribute('data-height');
                            var widthFromServer = wAttr ? parseInt(wAttr, 10) : null;
                            var heightFromServer = hAttr ? parseInt(hAttr, 10) : null;
                            if (widthFromServer && !isNaN(widthFromServer)) {
                                var widthToApply = Math.max(dashboardMinWidth, Math.min(dashboardMaxWidth, widthFromServer));
                                wrapper.style.width = widthToApply + 'px';
                                wrapper.style.flex = '0 0 ' + widthToApply + 'px';
                            }
                            if (heightFromServer && !isNaN(heightFromServer)) {
                                card.style.height = Math.max(dashboardMinHeight, heightFromServer) + 'px';
                            }
                        }

                        function onMouseMove(e) {
                            var deltaX = e.clientX - startX;
                            var deltaY = e.clientY - startY;
                            var newHeight = Math.max(dashboardMinHeight, startHeight + deltaY);
                            card.style.height = newHeight + 'px';
                            if (cardWrapper) {
                                var newWidth = Math.max(dashboardMinWidth, startWidth + deltaX);
                                newWidth = Math.min(dashboardMaxWidth, newWidth);
                                cardWrapper.style.width = newWidth + 'px';
                                cardWrapper.style.flex = '0 0 ' + newWidth + 'px';
                            }
                            var id = card.getAttribute('data-chart-id');
                            var chart = (window.__chartsById || {})[id];
                            if (chart) {
                                // AG Charts needs explicit resize handling with data preservation
                                setTimeout(() => {
                                    if (chart && typeof chart.update === 'function') {
                                        var storedConfig = (window.__chartConfigsById || {})[id];
                                        if (storedConfig) {
                                            // Update with stored data to preserve chart content
                                            chart.update({
                                                data: storedConfig.data,
                                                title: { text: storedConfig.title },
                                                series: storedConfig.options.series,
                                                axes: storedConfig.options.axes
                                            });
                                        } else {
                                            chart.update();
                                        }
                                    }
                                }, 100);
                            }
                        }

                        function persistSize(widthPx, heightPx) {
                            try {
                                var id = card.getAttribute('data-chart-id');
                                if (!id) return;
                                var url = '{{ route('dynamic-dashboard.charts.size', [$dashboard, ':id']) }}'.replace(':id', id);
                                var formData = new FormData();
                                formData.append('width_px', widthPx);
                                formData.append('height_px', heightPx);
                                fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: formData }).catch(function(){});
                            } catch (e) {}
                        }

                        var saveOnScroll = debounce(function () {
                            var finalHeight = parseInt(window.getComputedStyle(card).height, 10);
                            var finalWidth = cardWrapper ? parseInt(window.getComputedStyle(cardWrapper).width, 10) : null;
                            if (finalWidth) persistSize(finalWidth, finalHeight);
                        }, 500);

                        function onMouseUp() {
                            document.removeEventListener('mousemove', onMouseMove);
                            document.removeEventListener('mouseup', onMouseUp);
                            document.body.style.userSelect = '';
                            if (window.__chartsSortable && typeof window.__chartsSortable.option === 'function') {
                                window.__chartsSortable.option('disabled', false);
                            }
                            var finalHeight = parseInt(window.getComputedStyle(card).height, 10);
                            var finalWidth = cardWrapper ? parseInt(window.getComputedStyle(cardWrapper).width, 10) : null;
                            if (finalWidth) persistSize(finalWidth, finalHeight);
                            
                            // Force chart resize after mouse up
                            var id = card.getAttribute('data-chart-id');
                            var chart = (window.__chartsById || {})[id];
                            if (chart) {
                                setTimeout(() => {
                                    if (chart && typeof chart.update === 'function') {
                                        var storedConfig = (window.__chartConfigsById || {})[id];
                                        if (storedConfig) {
                                            // Update with stored data to preserve chart content
                                            chart.update({
                                                data: storedConfig.data,
                                                title: { text: storedConfig.title },
                                                series: storedConfig.options.series,
                                                axes: storedConfig.options.axes
                                            });
                                        } else {
                                            chart.update();
                                        }
                                    }
                                }, 200);
                            }
                        }

                        handle.addEventListener('mousedown', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            startX = e.clientX;
                            startY = e.clientY;
                            if (cardWrapper) {
                                startWidth = parseInt(window.getComputedStyle(cardWrapper).width, 10);
                            }
                            startHeight = parseInt(window.getComputedStyle(card).height, 10);
                            document.body.style.userSelect = 'none';
                            if (window.__chartsSortable && typeof window.__chartsSortable.option === 'function') {
                                window.__chartsSortable.option('disabled', true);
                            }
                            document.addEventListener('mousemove', onMouseMove);
                            document.addEventListener('mouseup', onMouseUp);
                        });
                    });
                }

                // Debounce helper
                function debounce(fn, wait) {
                    var t;
                    return function () {
                        clearTimeout(t);
                        var args = arguments;
                        var ctx = this;
                        t = setTimeout(function () { fn.apply(ctx, args); }, wait);
                    };
                }

                initResizableCharts();

                // Save size on container scroll (debounced) for all cards
                var debouncedScrollSave = debounce(function () {
                    var cards = container.querySelectorAll('.chart-resizable');
                    cards.forEach(function (card) {
                        var cardWrapper = card.closest('.chart-card');
                        var finalHeight = parseInt(window.getComputedStyle(card).height, 10);
                        var finalWidth = cardWrapper ? parseInt(window.getComputedStyle(cardWrapper).width, 10) : null;
                        var id = card.getAttribute('data-chart-id');
                        if (!id || !finalWidth) return;
                        try {
                            var url = '{{ route('dynamic-dashboard.charts.size', [$dashboard, ':id']) }}'.replace(':id', id);
                            var formData = new FormData();
                            formData.append('width_px', finalWidth);
                            formData.append('height_px', finalHeight);
                            fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: formData }).catch(function(){});
                        } catch (e) {}
                    });
                }, 750);

                container.addEventListener('scroll', debouncedScrollSave, { passive: true });
                
                // Handle window resize for all charts
                window.addEventListener('resize', debounce(function() {
                    Object.keys(window.__chartsById || {}).forEach(function(chartId) {
                        var chart = window.__chartsById[chartId];
                        if (chart && typeof chart.update === 'function') {
                            var storedConfig = (window.__chartConfigsById || {})[chartId];
                            if (storedConfig) {
                                // Update with stored data to preserve chart content
                                chart.update({
                                    data: storedConfig.data,
                                    title: { text: storedConfig.title },
                                    series: storedConfig.options.series,
                                    axes: storedConfig.options.axes
                                });
                            } else {
                                chart.update();
                            }
                        }
                    });
                }, 300));
            })();
        </script>

        <script>
            // Add validation for amount range fields
            function validateAmountRange(minField, maxField) {
                const minValue = parseFloat(minField.value) || 0;
                const maxValue = parseFloat(maxField.value) || 0;
                
                if (minField.value && maxField.value && minValue > maxValue) {
                    maxField.setCustomValidity('Maximum amount must be greater than or equal to minimum amount');
                    return false;
                } else {
                    maxField.setCustomValidity('');
                    return true;
                }
            }

            // Add event listeners for amount range validation
            document.addEventListener('DOMContentLoaded', function() {
                // For Add Chart modal
                const minRangeAdd = document.getElementById('amount_min_range');
                const maxRangeAdd = document.getElementById('amount_max_range');
                
                if (minRangeAdd && maxRangeAdd) {
                    minRangeAdd.addEventListener('input', () => validateAmountRange(minRangeAdd, maxRangeAdd));
                    maxRangeAdd.addEventListener('input', () => validateAmountRange(minRangeAdd, maxRangeAdd));
                }
                
                // For Edit Chart modal
                const minRangeEdit = document.getElementById('edit_amount_min_range');
                const maxRangeEdit = document.getElementById('edit_amount_max_range');
                
                if (minRangeEdit && maxRangeEdit) {
                    minRangeEdit.addEventListener('input', () => validateAmountRange(minRangeEdit, maxRangeEdit));
                    maxRangeEdit.addEventListener('input', () => validateAmountRange(minRangeEdit, maxRangeEdit));
                }
            });
        </script>
    </body>
</html>
