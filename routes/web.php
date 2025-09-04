<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\DistributorController;
use Illuminate\Support\Facades\DB;







Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();
Route::get('/connect_login', [\App\Http\Controllers\ExtLoginController::class, 'login']);
// Redirect /home to /dashboard for consistency
Route::get('/home', function () {
    return redirect()->route('dashboard');
});



Route::middleware('auth')->group(function () {
    // Change Password Routes (accessible to all authenticated users)
    Route::view('change-password', 'reset_password')->name('change-password');
    Route::post('update_password', [PasswordController::class, 'reset'])->name('update_password');

    // Distributor Routes (restricted to list-distributor permission)
    Route::middleware('permission:list-distributor')->group(function () {
        Route::resource('distributor', DistributorController::class);
        Route::get('/distributors/export', [DistributorController::class, 'export'])->name('distributor.export');
        Route::post('getDistributorList', [DistributorController::class, 'getDistributorList'])->name('getDistributorList');
    });





    //============================Builder=================================================
    Route::resource('page-builder', \App\Http\Controllers\PageBuilderController::class);
    Route::get('page-builder.page', [\App\Http\Controllers\PageBuilderController::class, 'formGenerate'])->name('page-builder.page');
    Route::post('add_form_element', [\App\Http\Controllers\PageBuilderController::class, 'addFormElement'])->name('add_form_element');
    Route::post('get_form_element_details', [\App\Http\Controllers\PageBuilderController::class, 'getFormElementDetails'])->name('get_form_element_details');
    Route::post('form_element_update', [\App\Http\Controllers\PageBuilderController::class, 'updateFormElement'])->name('form_element_update');
    Route::post('form_element_delete', [\App\Http\Controllers\PageBuilderController::class, 'deleteFormElement'])->name('form_element_delete');
    Route::post('generate_form', [\App\Http\Controllers\PageBuilderController::class, 'generateForm'])->name('generate_form');
    Route::post('update_sorting_order', [\App\Http\Controllers\PageBuilderController::class, 'updateSortingOrder'])->name('update_sorting_order');
    Route::resource('menu-builder', \App\Http\Controllers\MenuController::class);
    Route::post('menu-builder/setPosition', [\App\Http\Controllers\MenuController::class, 'setPosition'])->name('menu-builder.setPosition');
    Route::post('menu-builder/getParentMenus', [\App\Http\Controllers\MenuController::class, 'getParentMenus'])->name('menu-builder.getParentMenus');
    Route::post('menu-builder/show_menu', [\App\Http\Controllers\MenuController::class, 'show_menu'])->name('menu-builder.show_menu');
    Route::post('get_source_table_columns', [\App\Http\Controllers\PageBuilderController::class, 'getSourceTableColumns'])->name('get_source_table_columns');

    // Add API routes for filter dependencies
    Route::get('api/get-territory-regions/{territoryId}', function ($territoryId) {
        $regions = DB::table('core_region_territory_mapping')
            ->where('territory_id', $territoryId)
            ->join('core_region', 'core_region_territory_mapping.region_id', '=', 'core_region.id')
            ->where('core_region.is_active', 1)
            ->pluck('core_region.region_name', 'core_region.id')
            ->toArray();
        return response()->json(['regions' => $regions]);
    });

    Route::get('api/get-region-zones/{regionId}', function ($regionId) {
        $zones = DB::table('core_zone_region_mapping')
            ->where('region_id', $regionId)
            ->join('core_zone', 'core_zone_region_mapping.zone_id', '=', 'core_zone.id')
            ->where('core_zone.is_active', 1)
            ->pluck('core_zone.zone_name', 'core_zone.id')
            ->toArray();
        return response()->json(['zones' => $zones]);
    });
});

// Password Reset Routes (public)
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');




Route::resource('business_nature', \App\Http\Controllers\BusinessNature\BusinessNatureController::class);

Route::resource('legal_status', \App\Http\Controllers\LegalStatus\LegalStatusController::class);

Route::resource('gender', \App\Http\Controllers\Gender\GenderController::class);
Route::view('view_distributor', 'page_builder.distributor1')->name('view_distributor');

// routes/web.php

Route::middleware(['auth'])->group(function () {
    // Route::get('/test-email', function() {
    //     Mail::to('vnrit156t@gmail.com')->send(new \App\Mail\TestEmail());
    //     return "Email sent!";
    // });
});
