<?php

use App\Http\Controllers\OnboardingController;
use Illuminate\Support\Facades\Route;

Route::get('/onboardings-test', function() {
    return ['success' => true, 'message' => 'API is working!'];
});

    Route::get('/onboardings', [OnboardingController::class, 'getOnboardingDetails']);
