<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DynamicDashboardController;

Route::get('/', function () {
    return view('welcome');
});

// Dynamic Dashboard routes
Route::get('/dynamic-dashboard/create', [DynamicDashboardController::class, 'create'])->name('dynamic-dashboard.create');
Route::post('/dynamic-dashboard', [DynamicDashboardController::class, 'store'])->name('dynamic-dashboard.store');
// AJAX: fetch fields for selected module
Route::get('/dynamic-dashboard/module-fields', [DynamicDashboardController::class, 'moduleFields'])->name('dynamic-dashboard.module-fields');

// Store chart detail to a dashboard
Route::post('/dynamic-dashboard/{dashboard}/charts', [DynamicDashboardController::class, 'storeChartDetail'])
    ->whereNumber('dashboard')
    ->name('dynamic-dashboard.charts.store');

// Delete a specific chart detail from a dashboard
Route::delete('/dynamic-dashboard/{dashboard}/charts/{detail}', [DynamicDashboardController::class, 'destroyChartDetail'])
    ->whereNumber('dashboard')
    ->whereNumber('detail')
    ->name('dynamic-dashboard.charts.destroy');

// Update a specific chart detail in a dashboard
Route::put('/dynamic-dashboard/{dashboard}/charts/{detail}', [DynamicDashboardController::class, 'updateChartDetail'])
    ->whereNumber('dashboard')
    ->whereNumber('detail')
    ->name('dynamic-dashboard.charts.update');

// AJAX: save size for a specific chart card
Route::post('/dynamic-dashboard/{dashboard}/charts/{detail}/size', [DynamicDashboardController::class, 'saveSize'])
    ->whereNumber('dashboard')
    ->whereNumber('detail')
    ->name('dynamic-dashboard.charts.size');

// Show dashboard (must be after static routes)
Route::get('/dynamic-dashboard/{dashboard}', [DynamicDashboardController::class, 'show'])
    ->whereNumber('dashboard')
    ->name('dynamic-dashboard.show');

// AJAX: filtered chart data for a dashboard
Route::get('/dynamic-dashboard/{dashboard}/data', [DynamicDashboardController::class, 'data'])
    ->whereNumber('dashboard')
    ->name('dynamic-dashboard.data');
