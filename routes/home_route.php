<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

// Main dashboard route - accessible to all authenticated users
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');
Route::get('/dashboard/status-counts', [HomeController::class, 'statusCounts'])
	->name('dashboard.status-counts')
	->middleware('auth');
Route::post('notificationMarkRead', [HomeController::class, 'notificationMarkRead'])->name('notificationMarkRead')->middleware('auth');
Route::post('markAllRead', [HomeController::class, 'markAllRead'])->name('markAllRead')->middleware('auth');
Route::middleware(['auth'])->group(function () {
	Route::get('get-regions-by-zone', [App\Http\Controllers\HomeController::class, 'getRegionsByZone'])->name('get.regions.by.zone');
	Route::get('get-territories-by-region', [App\Http\Controllers\HomeController::class, 'getTerritoriesByRegion'])->name('get.territories.by.region');
	Route::get('/dashboard/dynamic-data', [App\Http\Controllers\HomeController::class, 'dynamicData'])->name('dashboard.dynamic-data');
});
