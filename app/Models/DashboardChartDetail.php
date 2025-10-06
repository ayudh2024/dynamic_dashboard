<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DashboardChartDetail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dashboard_id',
        'chart_id',
        'x_axis',
        'y_axis',
        'grid_columns',
        'module_name',
        'date_range',
        'amount_min_range',
        'amount_max_range',
        'width_px',
        'height_px',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'grid_columns' => 'array',
    ];

    /**
     * Get the dashboard that owns the detail.
     */
    public function dashboard(): BelongsTo
    {
        return $this->belongsTo(Dashboard::class);
    }

    /**
     * Get the chart associated with the detail.
     */
    public function chart(): BelongsTo
    {
        return $this->belongsTo(Chart::class);
    }
}


