<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\MISProcessingController;
use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function(){
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
});