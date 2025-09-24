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
                            <div class="chart-card border rounded-lg p-4 bg-white shadow w-[calc(50%-0.75rem)]">
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
                                            <button type="button" onclick="openEditChartModal({{ $cfg['id'] }}, {{ $cfg['chartId'] }}, '{{ $cfg['moduleName'] }}', '{{ $cfg['xLabel'] }}', '{{ $cfg['yLabel'] }}')" 
                           
                           
                           
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
                                        <label class="block text-xs font-medium text-gray-700">Filter By</label>
                                        <select name="date_field" class="date-field-select mt-1 block w-30 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="">Loading...</option>
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
                                <div class="relative group chart-resizable" data-chart-id="{{ $cfg['id'] }}" style="height: 16rem;">
                                    <canvas id="chart-{{ $cfg['id'] }}" style="width: 100%; height: 100%;"></canvas>
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

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" onclick="closeEditChartModal()" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700">Update</button>
                    </div>
                </form>
            </div>
        </div>

        @if(!empty($chartConfigs ?? []) && count($chartConfigs))
            @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
                
            @else
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            @endif
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

            function openEditChartModal(chartDetailId, chartId, moduleName, xLabel, yLabel) {
                // Set the form action
                editChartForm.action = '{{ route('dynamic-dashboard.charts.update', [$dashboard, ':chartDetailId']) }}'.replace(':chartDetailId', chartDetailId);
                
                // Set current values
                editChartIdSelect.value = chartId || '';
                editModuleSelect.value = moduleName || 'products';
                
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
                if (!Array.isArray(cfgs) || !cfgs.length || typeof Chart === 'undefined') return;
                window.__chartsById = window.__chartsById || {};
                cfgs.forEach(function (cfg) {
                    const el = document.getElementById('chart-' + cfg.id);
                    if (!el) return;
                    const isCategory = cfg.type !== 'scatter' && cfg.type !== 'bubble';
                    const dataset = {
                        label: cfg.title,
                        data: cfg.data,
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 1,
                        fill: cfg.type === 'line'
                    };
                    const chartInstance = new Chart(el, {
                        type: cfg.type,
                        data: { labels: cfg.labels, datasets: [dataset] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: true } },
                            scales: isCategory ? {
                                x: { title: { display: true, text: cfg.yLabel } },
                                y: { title: { display: true, text: cfg.xLabel }, beginAtZero: true }
                            } : {}
                        }
                    });
                    window.__chartsById[cfg.id] = chartInstance;
                });
            })();

            // Per-chart filtering
            (function () {
                const forms = document.querySelectorAll('.per-chart-filter');
                const moduleFieldsUrl = '{{ route('dynamic-dashboard.module-fields') }}';
                const dataUrlBase = '{{ route('dynamic-dashboard.data', $dashboard) }}';

                async function populateDateFields(form) {
                    const module = form.getAttribute('data-module');
                    const select = form.querySelector('select.date-field-select');
                    if (!module || !select) return;
                    try {
                        const res = await fetch(moduleFieldsUrl + '?' + new URLSearchParams({ module }).toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await res.json();
                        const options = (data.date && data.date.length ? data.date : ['sales_date']).map(function (name) {
                            const opt = document.createElement('option');
                            opt.value = name;
                            opt.textContent = name;
                            return opt;
                        });
                        select.innerHTML = '';
                        options.forEach(o => select.appendChild(o));
                    } catch (e) {
                        console.error(e);
                        select.innerHTML = '<option value="sales_date">sales_date</option>';
                    }
                }

                async function fetchAndRender(form) {
                    const detailId = form.getAttribute('data-detail-id');
                    const dateField = form.querySelector('select[name="date_field"]').value;
                    const from = form.querySelector('input[name="from"]').value;
                    const to = form.querySelector('input[name="to"]').value;
                    const params = new URLSearchParams();
                    if (detailId) params.set('detail_id', detailId);
                    if (dateField) params.set('date_field', dateField);
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
                            existing.config.type = cfg.type;
                            existing.data.labels = cfg.labels;
                            if (existing.data.datasets && existing.data.datasets[0]) {
                                existing.data.datasets[0].label = cfg.title;
                                existing.data.datasets[0].data = cfg.data;
                            }
                            existing.update();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                }

                forms.forEach(function (form) {
                    populateDateFields(form);
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        fetchAndRender(form);
                    });
                    const clearBtn = form.querySelector('.per-chart-clear');
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function () {
                            form.querySelector('input[name="from"]').value = '';
                            form.querySelector('input[name="to"]').value = '';
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
                    scrollSpeed: 10
                });
                window.__chartsSortable = sortable;

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

                        // Restore saved size if available
                        try {
                            var chartId = card.getAttribute('data-chart-id');
                            var savedRaw = localStorage.getItem('chartSize:' + chartId);
                            if (savedRaw) {
                                var saved = JSON.parse(savedRaw);
                                if (saved && typeof saved.width === 'number' && typeof saved.height === 'number') {
                                    card.style.height = Math.max(dashboardMinHeight, saved.height) + 'px';
                                    if (cardWrapper) {
                                        var widthToApply = Math.max(dashboardMinWidth, Math.min(dashboardMaxWidth, saved.width));
                                        cardWrapper.style.width = widthToApply + 'px';
                                        cardWrapper.style.flex = '0 0 ' + widthToApply + 'px';
                                    }
                                }
                            }
                        } catch (e) {
                            // ignore storage errors
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
                            if (chart && typeof chart.resize === 'function') {
                                chart.resize();
                            }
                        }

                        function onMouseUp() {
                            document.removeEventListener('mousemove', onMouseMove);
                            document.removeEventListener('mouseup', onMouseUp);
                            document.body.style.userSelect = '';
                            if (window.__chartsSortable && typeof window.__chartsSortable.option === 'function') {
                                window.__chartsSortable.option('disabled', false);
                            }

                            // Persist the final size
                            try {
                                var finalHeight = parseInt(window.getComputedStyle(card).height, 10);
                                var finalWidth = cardWrapper ? parseInt(window.getComputedStyle(cardWrapper).width, 10) : null;
                                var id = card.getAttribute('data-chart-id');
                                if (id && finalWidth) {
                                    localStorage.setItem('chartSize:' + id, JSON.stringify({ width: finalWidth, height: finalHeight }));
                                }
                            } catch (e) {
                                // ignore storage errors
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

                initResizableCharts();
            })();
        </script>
    </body>
</html>
