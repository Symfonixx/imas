<?php

use Illuminate\Support\Facades\Route;
use Modules\Corporate\Http\Controllers\Admin\CorporateServiceController;
use Modules\Corporate\Http\Controllers\Admin\TeamController;
use Modules\Corporate\Http\Controllers\Admin\TestimonialController;

Route::middleware('can:Corporate Management')->group(function () {
    Route::delete('corporate_services/deleteMulti', [CorporateServiceController::class, 'deleteMulti'])
        ->name('corporate_services.deleteMulti');
    Route::resource('corporate_services', CorporateServiceController::class)->except(['destroy', 'show']);

    Route::delete('corporate_testimonials/deleteMulti', [TestimonialController::class, 'deleteMulti'])
        ->name('corporate_testimonials.deleteMulti');
    Route::resource('corporate_testimonials', TestimonialController::class)
        ->parameters(['corporate_testimonials' => 'testimonial'])
        ->except(['destroy', 'show']);

    Route::delete('corporate_teams/deleteMulti', [TeamController::class, 'deleteMulti'])
        ->name('corporate_teams.deleteMulti');
    Route::resource('corporate_teams', TeamController::class)
        ->parameters(['corporate_teams' => 'team'])
        ->except(['destroy', 'show']);
});
