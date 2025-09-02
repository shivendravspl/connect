<?php

use App\Http\Controllers\VendorApprovalController;
use App\Http\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

// Add prefix and name prefix directly in this file
Route::prefix('vendors')->as('vendors.')->middleware('auth')->group(function(){
    // VendorController routes
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