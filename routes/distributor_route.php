<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DispatchController;

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function(){
   Route::get('/applications/pending-documents', [OnboardingController::class, 'pendingDocuments'])->name('applications.pending-documents');
   Route::post('/applications/pending-documents/{application}/upload', [OnboardingController::class, 'uploadPendingDocuments'])->name('applications.upload-pending-documents');
     Route::get('/applications/{application}/bank-details', [OnboardingController::class, 'getBankDetails'])->name('applications.bank-details');

	Route::resource('applications', OnboardingController::class);
    Route::post('applications/datatable', [OnboardingController::class, 'datatable'])->name('applications.datatable');
    Route::post('/applications/save-step/{stepNumber}', [OnboardingController::class, 'saveStep'])->name('applications.save-step');
    Route::get('/get-districts/{state_id}', [OnboardingController::class, 'getDistricts']);
    Route::get('/application/{id}/preview', [OnboardingController::class, 'preview'])->name('application.preview');
    Route::get('/application/{id}/download', [OnboardingController::class, 'downloadApplicationPdf'])->name('application.download');
    Route::get('/get-location-by-pincode/{pincode}', [OnboardingController::class, 'getLocationByPincode'])->name('location.by-pincode');
    // Approval routes
    Route::prefix('approvals')->group(function () {
        Route::get('/dashboard', [ApprovalController::class, 'dashboard'])->name('approvals.dashboard');
        Route::get('/{application}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/{application}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/{application}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/{application}/revert', [ApprovalController::class, 'revert'])->name('approvals.revert');
        Route::post('/{application}/hold', [ApprovalController::class, 'hold'])->name('approvals.hold');
    });

    Route::post('/process-pan-card', [DocumentController::class, 'processPANCard'])->name('process-pan-card');
    Route::post('/process-bank-document', [DocumentController::class, 'processBankDocument'])->name('process.bank.document');
    Route::post('/process-seed-license', [DocumentController::class, 'processSeedLicense'])->name('process-seed-license');
    Route::post('/process-document', [DocumentController::class, 'processDocument'])->name('process-document');
    Route::post('/process-gst-document', [DocumentController::class, 'processGSTDocument'])->name('process-gst-document');
    Route::post('/process-letter-document', [DocumentController::class, 'processLetterDocument'])->name('process-letter-document');
    Route::post('/process-aadhar-document', [DocumentController::class, 'processAadharDocument'])->name('process-aadhar-document');
    Route::post('/process-partner-aadhar', [DocumentController::class, 'processPartnerAadhar'])->name('process-aadhar-document');

    Route::post('/fetch-bank-details', [DocumentController::class, 'fetchDetails']);

    Route::get('/mis/verification', [ApprovalController::class, 'misVerificationList'])
    ->name('mis.verification-list');
    Route::get('/approvals/{application}/verify-documents', [ApprovalController::class, 'verifyDocuments'])->name('approvals.verify-documents');
    Route::post('/approvals/{application}/update-documents', [ApprovalController::class, 'updateDocuments'])->name('approvals.update-documents');
    Route::get('/approvals/{application}/view-checklist', [ApprovalController::class, 'viewChecklist'])->name('approvals.view-checklist');
    Route::post('approvals/{application}/update-entity-details', [ApprovalController::class, 'updateEntityDetails'])->name('approvals.update-entity-details');


    Route::get('/applications/{id}/dispatch', [DispatchController::class, 'show'])->name('dispatch.show');
    Route::post('/applications/{id}/dispatch', [DispatchController::class, 'store'])->name('dispatch.store');  

    Route::get('approvals/{application}/physical-documents', [ApprovalController::class, 'showPhysicalDocuments'])->name('approvals.physical-documents');
    Route::post('approvals/{application}/update-physical-documents', [ApprovalController::class, 'updatePhysicalDocuments'])->name('approvals.update-physical-documents');

    Route::get('/approvals/{application}/view-doc-verification', [ApprovalController::class, 'viewDocVerification'])->name('approvals.view-doc-verification');
    Route::get('/approvals/{application}/view-physical-doc-verification', [ApprovalController::class, 'viewPhysicalDocVerification'])->name('approvals.view-physical-doc-verification');
    Route::post('/approvals/{application}/confirm-distributor', [ApprovalController::class, 'confirmDistributor'])->name('approvals.confirm-distributor');
    

    Route::get('/mis/applications', [ApprovalController::class, 'applications'])->name('mis.applications');
    // For Approver/Admin users  
    Route::get('/approver/applications', [ApprovalController::class, 'applications'])->name('approver.applications');
});