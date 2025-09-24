<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\Chart;
use App\Models\DashboardChartDetail;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DynamicDashboardController extends Controller
{
    /**
     * Show the form to create a dynamic dashboard.
     */
    public function create(): View
    {
        return view('dynamic-dashboard.create');
    }

    /**
     * Handle form submission for creating a dynamic dashboard.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        // Create and persist dashboard data
        $dashboard = Dashboard::create($validated);

        return redirect()
            ->route('dynamic-dashboard.show', $dashboard)
            ->with('status', 'Dynamic dashboard "' . $validated['title'] . '" created successfully!');
    }

    /**
     * Display the specified dashboard.
     */
    public function show(Request $request, Dashboard $dashboard): View
    {
        $charts = Chart::orderBy('name')->get(['id', 'name']);

        $details = DashboardChartDetail::with('chart')
            ->where('dashboard_id', $dashboard->id)
            ->get();

        $chartTypeMap = [
            'Pie Chart' => 'pie',
            'Bar Chart' => 'bar',
            'Line Chart' => 'line',
            'Area Chart' => 'line',
            'Scatter Chart' => 'scatter',
            'Doughnut Chart' => 'doughnut',
            'Radar Chart' => 'radar',
            'Bubble Chart' => 'bubble',
            'Polar Area Chart' => 'polarArea',
        ];

        $chartConfigs = [];

        $from = $request->query('from');
        $to = $request->query('to');

        foreach ($details as $detail) {
            $chartName = $detail->chart?->name ?? 'Bar Chart';
            $type = $chartTypeMap[$chartName] ?? 'bar';
            
            $labels = [];
            $data = [];

            if ($detail->module_name === 'products' && $detail->x_axis && $detail->y_axis) {
                $rows = Product::query()
                    ->when($from, function ($q) use ($from) {
                        $q->whereDate('sales_date', '>=', $from);
                    })
                    ->when($to, function ($q) use ($to) {
                        $q->whereDate('sales_date', '<=', $to);
                    })
                    ->selectRaw("{$detail->y_axis} as label, SUM({$detail->x_axis}) as value")
                    ->groupBy($detail->y_axis)
                    ->orderBy('label')
                    ->get();
                foreach ($rows as $row) {
                    $labels[] = (string) $row->label;
                    $data[] = is_numeric($row->value) ? (float) $row->value : null;
                }
            }

            $chartConfigs[] = [
                'id' => $detail->id,
                'type' => $type,
                'title' => $chartName,
                'labels' => $labels,
                'data' => $data,
                'xLabel' => $detail->x_axis,
                'yLabel' => $detail->y_axis,
                'chartId' => $detail->chart_id,
                'moduleName' => $detail->module_name,
            ];
        }

        return view('dynamic-dashboard.show', compact('dashboard', 'charts', 'details', 'chartConfigs'));
    }

    /**
     * Return available field lists for a given module.
     */
    public function moduleFields(Request $request)
    {
        $module = $request->query('module');
        if (! $module || ! Schema::hasTable($module)) {
            return response()->json(['numeric' => [], 'string' => [], 'date' => []]);
        }

        $driver = DB::getDriverName();

        // PostgreSQL: concise metadata query
        if ($driver === 'pgsql') {
            $rows = DB::select(
                'SELECT column_name AS name, data_type AS type
                 FROM information_schema.columns
                 WHERE table_schema = current_schema() AND table_name = ?
                 ORDER BY ordinal_position',
                [$module]
            );

            $numericTypes = ['smallint', 'integer', 'bigint', 'decimal', 'numeric', 'real', 'double precision'];
            $stringTypes = ['character varying', 'varchar', 'character', 'char', 'text', 'json', 'jsonb', 'uuid', 'date'];
            $dateTypes = ['timestamp without time zone', 'timestamp with time zone', 'time without time zone', 'time with time zone'];

            $numeric = [];
            $string = [];
            $date = [];

            foreach ($rows as $r) {
                $name = (string) $r->name;
                $type = strtolower((string) $r->type);
                if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                    continue;
                }
                if (in_array($type, $numericTypes, true)) {
                    $numeric[] = $name;
                } elseif (in_array($type, $dateTypes, true)) {
                    $date[] = $name;
                } elseif (in_array($type, $stringTypes, true)) {
                    $string[] = $name;
                } else {
                    $string[] = $name;
                }
            }

            return response()->json(['numeric' => $numeric, 'string' => $string, 'date' => $date]);
        }

        // Fallback: names only
        $names = Schema::getColumnListing($module);
        $names = array_values(array_diff($names, ['id', 'created_at', 'updated_at', 'deleted_at']));
        $dateHeuristic = array_values(array_filter($names, function ($n) {
            return preg_match('/date|_at$/i', (string) $n) === 1;
        }));
        return response()->json(['numeric' => [], 'string' => $names, 'date' => $dateHeuristic]);
    }

    /**
     * Store a new chart detail configuration for a dashboard.
     */
    public function storeChartDetail(Request $request, Dashboard $dashboard): RedirectResponse
    {
        $validated = $request->validate([
            'chart_id' => ['required', 'exists:charts,id'],
            'module_name' => ['required', 'string', 'max:255'],
            'x_axis' => ['nullable', 'string', 'max:255'],
            'y_axis' => ['nullable', 'string', 'max:255'],
        ]);

        DashboardChartDetail::create([
            'dashboard_id' => $dashboard->id,
            'chart_id' => $validated['chart_id'],
            'x_axis' => $validated['x_axis'] ?? null,
            'y_axis' => $validated['y_axis'] ?? null,
            'module_name' => $validated['module_name'],
        ]);

        return redirect()
            ->route('dynamic-dashboard.show', $dashboard)
            ->with('status', 'Chart added to dashboard successfully.');
    }

    /**
     * Update a specific chart detail in the dashboard.
     */
    public function updateChartDetail(Request $request, Dashboard $dashboard, DashboardChartDetail $detail): RedirectResponse
    {
        if ((int) $detail->dashboard_id !== (int) $dashboard->id) {
            return redirect()
                ->route('dynamic-dashboard.show', $dashboard)
                ->with('status', 'Unable to update: Chart does not belong to this dashboard.');
        }

        $validated = $request->validate([
            'chart_id' => ['required', 'exists:charts,id'],
            'module_name' => ['required', 'string', 'max:255'],
            'x_axis' => ['nullable', 'string', 'max:255'],
            'y_axis' => ['nullable', 'string', 'max:255'],
        ]);

        $detail->update([
            'chart_id' => $validated['chart_id'],
            'module_name' => $validated['module_name'],
            'x_axis' => $validated['x_axis'] ?? null,
            'y_axis' => $validated['y_axis'] ?? null,
        ]);

        return redirect()
            ->route('dynamic-dashboard.show', $dashboard)
            ->with('status', 'Chart updated successfully.');
    }

    /**
     * Delete a specific chart detail from the dashboard.
     */
    public function destroyChartDetail(Request $request, Dashboard $dashboard, DashboardChartDetail $detail): RedirectResponse
    {
        if ((int) $detail->dashboard_id !== (int) $dashboard->id) {
            return redirect()
                ->route('dynamic-dashboard.show', $dashboard)
                ->with('status', 'Unable to delete: Chart does not belong to this dashboard.');
        }

        $detail->delete();

        return redirect()
            ->route('dynamic-dashboard.show', $dashboard)
            ->with('status', 'Chart removed from dashboard.');
    }

    /**
     * Return chart data for a dashboard (AJAX).
     */
    public function data(Request $request, Dashboard $dashboard)
    {
        $details = DashboardChartDetail::with('chart')
            ->where('dashboard_id', $dashboard->id)
            ->get();

        $chartTypeMap = [
            'Pie Chart' => 'pie',
            'Bar Chart' => 'bar',
            'Line Chart' => 'line',
            'Area Chart' => 'line',
            'Scatter Chart' => 'scatter',
            'Doughnut Chart' => 'doughnut',
            'Radar Chart' => 'radar',
            'Bubble Chart' => 'bubble',
            'Polar Area Chart' => 'polarArea',
        ];

        $from = $request->query('from');
        $to = $request->query('to');
        $detailId = $request->query('detail_id');
        $dateFieldOverride = $request->query('date_field');

        $chartConfigs = [];

        foreach ($details as $detail) {
            if ($detailId && (int) $detailId !== (int) $detail->id) {
                continue;
            }
            $chartName = $detail->chart?->name ?? 'Bar Chart';
            $type = $chartTypeMap[$chartName] ?? 'bar';

            $labels = [];
            $data = [];

            if ($detail->module_name === 'products' && $detail->x_axis && $detail->y_axis) {
                $dateField = $dateFieldOverride ?: 'sales_date';
                $rows = Product::query()
                    ->when($from, function ($q) use ($from, $dateField) {
                        $q->whereDate($dateField, '>=', $from);
                    })
                    ->when($to, function ($q) use ($to, $dateField) {
                        $q->whereDate($dateField, '<=', $to);
                    })
                    ->selectRaw("{$detail->y_axis} as label, SUM({$detail->x_axis}) as value")
                    ->groupBy($detail->y_axis)
                    ->orderBy('label')
                    ->get();
                foreach ($rows as $row) {
                    $labels[] = (string) $row->label;
                    $data[] = is_numeric($row->value) ? (float) $row->value : null;
                }
            }

            $chartConfigs[] = [
                'id' => $detail->id,
                'type' => $type,
                'title' => $chartName,
                'labels' => $labels,
                'data' => $data,
                'xLabel' => $detail->x_axis,
                'yLabel' => $detail->y_axis,
                'chartId' => $detail->chart_id,
                'moduleName' => $detail->module_name,
            ];
        }

        return response()->json(['charts' => $chartConfigs]);
    }
}
