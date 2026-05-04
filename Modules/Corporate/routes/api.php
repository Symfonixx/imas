<?php

use Illuminate\Support\Facades\Route;
use Modules\Corporate\Http\Controllers\Api\CorporateServiceController;
use Modules\Corporate\Http\Controllers\Api\TeamController;
use Modules\Corporate\Http\Controllers\Api\TestimonialController;
use Modules\Corporate\Http\Controllers\CorporateController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 */

Route::get('corporate-services', [CorporateServiceController::class, 'index'])->name('corporate-services.index');

Route::get('corporate-testimonials', [TestimonialController::class, 'index'])
    ->name('corporate-testimonials.index');

Route::get('corporate-teams', [TeamController::class, 'index'])
    ->name('corporate-teams.index');

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('corporate', CorporateController::class)->names('corporate');
});
