<?php

use App\Http\Controllers\CoreAPIController;
use App\Http\Controllers\CoreCompanyController;
use App\Http\Controllers\CoreOrgFunctionController;
use App\Http\Controllers\CoreBusinessUnitController;
use App\Http\Controllers\CoreCategoryController;
use App\Http\Controllers\CoreCropController;
use App\Http\Controllers\CoreZoneController;
use App\Http\Controllers\CoreRegionController;
use App\Http\Controllers\CoreVarietyController;
use App\Http\Controllers\CoreVerticalController;
use App\Http\Controllers\CoreTerritoryController;

use Illuminate\Support\Facades\Route;

Route::middleware('permission:list-core-api')->group(function () {
	Route::resource('core_api', CoreAPIController::class);
	Route::get('core_api_sync', [CoreAPIController::class, 'sync'])->name('core_api_sync');
	Route::post('importAPISData', [CoreAPIController::class, 'importAPISData'])->name('importAPISData');
});
// Company Routes (restricted to list-company permission)
Route::middleware('permission:list-company')->group(function () {
	Route::resource('companies', CoreCompanyController::class)->only(['index']);
	Route::get('/companies/export', [CoreCompanyController::class, 'export'])->name('companies.export');
});
// Organization Function Routes (restricted to list-org-function permission)
Route::middleware('permission:list-org-function')->group(function () {
	Route::resource('org-functions', CoreOrgFunctionController::class)->only(['index']);
	Route::get('/org-functions/export', [CoreOrgFunctionController::class, 'export'])->name('org-functions.export');
});
// Business Unit Routes (restricted to list-business-unit permission)
Route::middleware('permission:list-business-unit')->group(function () {
	Route::resource('business-units', CoreBusinessUnitController::class)->only(['index']);
	Route::get('/business-units/export', [CoreBusinessUnitController::class, 'export'])->name('business-units.export');
	Route::post('business-units/getBusinessUnitList', [CoreBusinessUnitController::class, 'getBusinessUnitList'])->name('business-units.getBusinessUnitList');
});
// Category Routes (restricted to list-category permission)
Route::middleware('permission:list-category')->group(function () {
	Route::resource('categories', CoreCategoryController::class)->only(['index']);
	Route::get('/categories/export', [CoreCategoryController::class, 'export'])->name('categories.export');
});

// Crop Routes (restricted to list-crop permission)
Route::middleware('permission:list-crop')->group(function () {
	Route::resource('crops', CoreCropController::class)->only(['index']);
	Route::get('/crops/export', [CoreCropController::class, 'export'])->name('crops.export');
	Route::post('crops/getCropList', [CoreCropController::class, 'getCropList'])->name('crops.getCropList');
});
// Zone Routes (restricted to list-zone permission)
Route::middleware('permission:list-zone')->group(function () {
	Route::get('zones', [CoreZoneController::class, 'index'])->name('zones.index');
	Route::get('/zones/export', [CoreZoneController::class, 'export'])->name('zones.export');
	Route::post('zones/getZoneList', [CoreZoneController::class, 'getZoneList'])->name('zones.getZoneList');
});

// Region Routes (restricted to list-region permission)
Route::middleware('permission:list-region')->group(function () {
	Route::resource('regions', CoreRegionController::class)->only(['index']);
	Route::get('/regions/export', [CoreRegionController::class, 'export'])->name('regions.export');
	Route::post('regions/getRegionList', [CoreRegionController::class, 'getRegionList'])->name('regions.getRegionList');
});

// Territory Routes (restricted to list-territory permission)
Route::middleware('permission:list-territory')->group(function () {
	Route::resource('territories', CoreTerritoryController::class)->only(['index']);
	Route::get('/territories/export', [CoreTerritoryController::class, 'export'])->name('territories.export');
	Route::post('territories/getTerritoryList', [CoreTerritoryController::class, 'getTerritoryList'])->name('territories.getTerritoryList');
});



// Variety Routes (restricted to list-variety permission)
Route::middleware('permission:list-variety')->group(function () {
	Route::resource('varieties', CoreVarietyController::class)->only(['index']);
	Route::get('/varieties/export', [CoreVarietyController::class, 'export'])->name('varieties.export');
	Route::post('varieties/getVarietyList', [CoreVarietyController::class, 'getVarietyList'])->name('varieties.getVarietyList');
});

// Vertical Routes (restricted to list-vertical permission)
Route::middleware('permission:list-vertical')->group(function () {
	Route::resource('verticals', CoreVerticalController::class)->only(['index']); // Changed to resource
	Route::get('/verticals/export', [CoreVerticalController::class, 'export'])->name('verticals.export');
});

// Add these new API routes
Route::post('get_zone_by_bu', [CoreZoneController::class, 'get_zone_by_bu'])->name('get_zone_by_bu');
Route::post('get_region_by_zone', [CoreRegionController::class, 'get_region_by_zone'])->name('get_region_by_zone');
Route::post('get_territory_by_region', [CoreTerritoryController::class, 'get_territory_by_region'])->name('get_territory_by_region');

Route::middleware(['auth'])->group(function () {
	Route::get('/get-territory-data', [CoreTerritoryController::class, 'getMappingData']);
});
