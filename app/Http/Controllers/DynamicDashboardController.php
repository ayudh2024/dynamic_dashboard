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
use Carbon\Carbon;

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
            ->orderBy('sort_order')
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
                // Apply default date range if no specific filter is provided
                $dateRange = null;
                if (!$from && !$to && $detail->date_range) {
                    $dateRange = $this->calculateDateRange($detail->date_range);
                    $from = $dateRange['from']?->format('Y-m-d');
                    $to = $dateRange['to']?->format('Y-m-d');
                }
                
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
                'dateRange' => $detail->date_range,
                'widthPx' => $detail->width_px,
                'heightPx' => $detail->height_px,
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
            'date_range' => ['nullable', 'string', 'in:last_7_days,this_week,last_15_days,this_month,last_month,this_year'],
        ]);

        // Get the next sort order for this dashboard
        $maxSortOrder = DashboardChartDetail::where('dashboard_id', $dashboard->id)
            ->max('sort_order') ?? 0;

        DashboardChartDetail::create([
            'dashboard_id' => $dashboard->id,
            'chart_id' => $validated['chart_id'],
            'x_axis' => $validated['x_axis'] ?? null,
            'y_axis' => $validated['y_axis'] ?? null,
            'module_name' => $validated['module_name'],
            'date_range' => $validated['date_range'] ?? null,
            'sort_order' => $maxSortOrder + 1,
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
            'date_range' => ['nullable', 'string', 'in:last_7_days,this_week,last_15_days,this_month,last_month,this_year'],
        ]);

        $detail->update([
            'chart_id' => $validated['chart_id'],
            'module_name' => $validated['module_name'],
            'x_axis' => $validated['x_axis'] ?? null,
            'y_axis' => $validated['y_axis'] ?? null,
            'date_range' => $validated['date_range'] ?? null,
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
            ->orderBy('sort_order')
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
        $dateRangeOverride = $request->query('date_range');

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
                
                // Apply date range (override or default) if no specific from/to dates are provided
                if (!$from && !$to) {
                    $rangeToUse = $dateRangeOverride ?: $detail->date_range;
                    if ($rangeToUse) {
                        $dateRange = $this->calculateDateRange($rangeToUse);
                        $from = $dateRange['from']?->format('Y-m-d');
                        $to = $dateRange['to']?->format('Y-m-d');
                    }
                }
                
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
                'dateRange' => $detail->date_range,
                'widthPx' => $detail->width_px,
                'heightPx' => $detail->height_px,
            ];
        }

        return response()->json(['charts' => $chartConfigs]);
    }

    /**
     * Persist chart card size (AJAX).
     */
    public function saveSize(Request $request, Dashboard $dashboard, DashboardChartDetail $detail)
    {
        if ((int) $detail->dashboard_id !== (int) $dashboard->id) {
            return response()->json(['message' => 'Chart does not belong to this dashboard'], 422);
        }

        $validated = $request->validate([
            'width_px' => ['nullable', 'integer', 'min:100', 'max:4096'],
            'height_px' => ['nullable', 'integer', 'min:100', 'max:4096'],
        ]);

        $detail->fill($validated);
        $detail->save();

        return response()->json(['message' => 'saved']);
    }

    /**
     * Update chart order (AJAX).
     */
    public function updateOrder(Request $request, Dashboard $dashboard)
    {
        $validated = $request->validate([
            'chart_ids' => ['required', 'array'],
            'chart_ids.*' => ['required', 'integer', 'exists:dashboard_chart_details,id'],
        ]);

        // Verify all chart details belong to this dashboard
        $chartDetails = DashboardChartDetail::whereIn('id', $validated['chart_ids'])
            ->where('dashboard_id', $dashboard->id)
            ->get();

        if ($chartDetails->count() !== count($validated['chart_ids'])) {
            return response()->json(['message' => 'Some charts do not belong to this dashboard'], 422);
        }

        // Update sort order based on the new order
        foreach ($validated['chart_ids'] as $index => $chartId) {
            DashboardChartDetail::where('id', $chartId)
                ->where('dashboard_id', $dashboard->id)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['message' => 'Chart order updated successfully']);
    }

    /**
     * Calculate date range based on the provided range type.
     */
    private function calculateDateRange(string $dateRange): array
    {
        $now = Carbon::now();
        
        switch ($dateRange) {
            case 'last_7_days':
                return [
                    'from' => $now->copy()->subDays(7)->startOfDay(),
                    'to' => $now->copy()->endOfDay()
                ];
            
            case 'this_week':
                return [
                    'from' => $now->copy()->startOfWeek(),
                    'to' => $now->copy()->endOfWeek()
                ];
            
            case 'last_15_days':
                return [
                    'from' => $now->copy()->subDays(15)->startOfDay(),
                    'to' => $now->copy()->endOfDay()
                ];
            
            case 'this_month':
                return [
                    'from' => $now->copy()->startOfMonth(),
                    'to' => $now->copy()->endOfMonth()
                ];
            
            case 'last_month':
                return [
                    'from' => $now->copy()->subMonth()->startOfMonth(),
                    'to' => $now->copy()->subMonth()->endOfMonth()
                ];
            
            case 'this_year':
                return [
                    'from' => $now->copy()->startOfYear(),
                    'to' => $now->copy()->endOfYear()
                ];
            
            default:
                return ['from' => null, 'to' => null];
        }
    }
}
