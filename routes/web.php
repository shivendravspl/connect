<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Roles_Permission\PermissionController;
use App\Http\Controllers\Roles_Permission\RoleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\CoreAPIController;
use App\Http\Controllers\DistributorController; // Fixed typo
use App\Http\Controllers\CoreOrgFunctionController;
use App\Http\Controllers\CoreBusinessUnitController;
use App\Http\Controllers\CoreCategoryController;
use App\Http\Controllers\CoreCompanyController;
use App\Http\Controllers\CoreCropController;
use App\Http\Controllers\CoreRegionController;
use App\Http\Controllers\CoreTerritoryController;
use App\Http\Controllers\CoreVarietyController;
use App\Http\Controllers\CoreVerticalController;
use App\Http\Controllers\CoreZoneController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApprovalController;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\MISProcessingController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorApprovalController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\ItemController;





Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

// Redirect /home to /dashboard for consistency
Route::get('/home', function () {
    return redirect()->route('dashboard');
});

// Main dashboard route - accessible to all authenticated users
Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware('auth');
Route::get('/dashboard/status-counts', [HomeController::class, 'statusCounts'])
    ->name('dashboard.status-counts')
    ->middleware('auth');
Route::post('notificationMarkRead', [HomeController::class, 'notificationMarkRead'])->name('notificationMarkRead')->middleware('auth');
Route::post('markAllRead', [HomeController::class, 'markAllRead'])->name('markAllRead')->middleware('auth');



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

    // User Routes (restricted to list-user permission)
    Route::middleware('permission:list-user')->group(function () {
        Route::resource('users', UserController::class);
        Route::post('getUserList', [UserController::class, 'getUserList'])->name('getUserList');
        Route::post('/users/export', [UserController::class, 'export'])->name('users.export');
    });

    // Role and Permission Routes (restricted to list-role permission)
    Route::middleware('permission:list-role')->group(function () {
        Route::resource('roles', RoleController::class);
        Route::get('permission', [PermissionController::class, 'index'])->name('permission');
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

    // Business Unit Routes (restricted to list-business-unit permission)
    Route::middleware('permission:list-business-unit')->group(function () {
        Route::resource('business-units', CoreBusinessUnitController::class)->only(['index']);
        Route::get('/business-units/export', [CoreBusinessUnitController::class, 'export'])->name('business-units.export');
        Route::post('business-units/getBusinessUnitList', [CoreBusinessUnitController::class, 'getBusinessUnitList'])->name('business-units.getBusinessUnitList');
    });

    // Organization Function Routes (restricted to list-org-function permission)
    Route::middleware('permission:list-org-function')->group(function () {
        Route::resource('org-functions', CoreOrgFunctionController::class)->only(['index']);
        Route::get('/org-functions/export', [CoreOrgFunctionController::class, 'export'])->name('org-functions.export');
    });

    // Company Routes (restricted to list-company permission)
    Route::middleware('permission:list-company')->group(function () {
        Route::resource('companies', CoreCompanyController::class)->only(['index']); // Changed to resource
        Route::get('/companies/export', [CoreCompanyController::class, 'export'])->name('companies.export');
    });

    // Core API Routes (restricted to list-core-api permission)
    Route::middleware('permission:list-core-api')->group(function () {
        Route::resource('core_api', CoreAPIController::class);
        Route::get('core_api_sync', [CoreAPIController::class, 'sync'])->name('core_api_sync');
        Route::post('importAPISData', [CoreAPIController::class, 'importAPISData'])->name('importAPISData');
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

    // In your routes file
    Route::post('/get-regions', [EmployeeController::class, 'getRegionsByTerritory']);
    Route::post('/get-zones', [EmployeeController::class, 'getZonesByRegion']);

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

    // Add these new API routes
    Route::post('get_zone_by_bu', [CoreZoneController::class, 'get_zone_by_bu'])->name('get_zone_by_bu');
    Route::post('get_region_by_zone', [CoreRegionController::class, 'get_region_by_zone'])->name('get_region_by_zone');
    Route::post('get_territory_by_region', [CoreTerritoryController::class, 'get_territory_by_region'])->name('get_territory_by_region');

    Route::get('get-regions-by-zone', [App\Http\Controllers\HomeController::class, 'getRegionsByZone'])->name('get.regions.by.zone');
    Route::get('get-territories-by-region', [App\Http\Controllers\HomeController::class, 'getTerritoriesByRegion'])->name('get.territories.by.region');
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
    // Application routes
    Route::resource('applications', OnboardingController::class);
    Route::post('/applications/save-step/{stepNumber}', [OnboardingController::class, 'saveStep'])->name('applications.save-step');
    Route::post('/applications/remove-document/{application_id}', [OnboardingController::class, 'removeDocument'])->name('applications.remove-document');

    Route::get('/get-districts/{state_id}', [OnboardingController::class, 'getDistricts']);
    Route::get('/application/{id}/preview', [OnboardingController::class, 'preview'])->name('application.preview');
    Route::get('/application/{id}/download', [OnboardingController::class, 'downloadApplicationPdf'])->name('application.download');
    // Approval routes
    Route::prefix('approvals')->group(function () {
        Route::get('/dashboard', [ApprovalController::class, 'dashboard'])->name('approvals.dashboard');
        Route::get('/{application}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/{application}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/{application}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/{application}/revert', [ApprovalController::class, 'revert'])->name('approvals.revert');
        Route::post('/{application}/hold', [ApprovalController::class, 'hold'])->name('approvals.hold');
    });

    // MIS processing routes
    Route::prefix('mis')->group(function () {
        Route::get('/{application}/verify-documents', [MISProcessingController::class, 'showDocumentVerification'])->name('approvals.verify-documents');
        Route::post('/{application}/verify-documents', [MISProcessingController::class, 'verifyDocuments'])->name('approvals.submit-verification');
        Route::get('/{application}/upload-agreement', [MISProcessingController::class, 'showAgreementUpload'])->name('approvals.upload-agreement');
        Route::post('/{application}/generate-agreement', [MISProcessingController::class, 'generateAgreement'])->name('approvals.generate-agreement');
        Route::get('/{application}/track-documents', [MISProcessingController::class, 'showPhysicalDocumentTracking'])->name('approvals.track-documents');
        Route::post('/{application}/track-documents', [MISProcessingController::class, 'trackPhysicalDocuments'])->name('approvals.submit-documents');
    });

    // Route::get('/test-email', function() {
    //     Mail::to('vnrit156t@gmail.com')->send(new \App\Mail\TestEmail());
    //     return "Email sent!";
    // });
    Route::get('/get-territory-data', [CoreTerritoryController::class, 'getMappingData']);
    Route::get('/dashboard/dynamic-data', [App\Http\Controllers\HomeController::class, 'dynamicData'])->name('dashboard.dynamic-data');

    Route::prefix('vendors')->name('vendors.')->group(function () {
        // Routes handled by VendorController
        Route::post('/store-section/{vendor}', [VendorController::class, 'storeSection'])->name('store.section');
        Route::get('/list', [VendorController::class, 'index'])->name('index');
        Route::get('/create', [VendorController::class, 'create'])->name('create');
        Route::post('/store', [VendorController::class, 'store'])->name('store');
        Route::get('/profile', [VendorController::class, 'profile'])->name('profile');
        Route::get('/edit/{id}', [VendorController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [VendorController::class, 'update'])->name('update');
        Route::get('/edit/{vendor}/section/{section}', [VendorController::class, 'editSection'])->name('edit.section');
        Route::delete('/destroy/{id}', [VendorController::class, 'destroy'])->name('destroy');
        Route::get('/submitted/{id}', [VendorController::class, 'submitted'])->name('submitted');
        Route::get('/success/{id}', [VendorController::class, 'success'])->name('success');
        Route::get('/{id}', [VendorController::class, 'show'])->name('show');
        Route::get('/employees/by-department/{departmentId}', [VendorController::class, 'getEmployee'])->name('employees.by-department');
        Route::get('/{id}/documents/{type}', [VendorController::class, 'showDocument'])->name('documents.show');
        Route::post('/{id}/toggle-active', [VendorController::class, 'toggleActive'])->name('toggle-active');
    });

    Route::middleware(['auth'])->group(function () {
        // Routes handled by VendorApprovalController
        Route::get('/temp-edits', [VendorApprovalController::class, 'tempEdits'])->name('temp-edits');
        Route::get('/temp-edits/{id}', [VendorApprovalController::class, 'showTempEdit'])->name('temp-edits.show');
        Route::patch('/temp-edits/approve/{id}', [VendorApprovalController::class, 'approveTempEdit'])->name('temp-edits.approve');
        Route::patch('/temp-edits/reject/{id}', [VendorApprovalController::class, 'rejectTempEdit'])->name('temp-edits.reject');
        Route::get('/temp-edits/document/{id}/{type}', [VendorApprovalController::class, 'showTempDocument'])->name('temp-document');
        Route::post('/{id}/approve', [VendorApprovalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [VendorApprovalController::class, 'reject'])->name('reject');
    });

    Route::middleware(['auth'])->group(function () {
          Route::get('/items/export', [ItemController::class, 'exportItems'])->name('items.export');
        Route::get('/categories/export', [ItemController::class, 'exportCategories'])->name('categories.export');
        Route::resource('item-groups', ItemGroupController::class);
        Route::resource('items', ItemController::class);

        Route::delete('categories/{category}', [ItemController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('items/{item}/categories', [ItemController::class, 'getItemCategories'])->name('items.categories.get');
        Route::post('items/{item}/categories', [ItemController::class, 'updateItemCategories'])->name('items.categories.update');
      
    });
});
