<?php

use Illuminate\Support\Facades\Route;
use Modules\Property\Http\Controllers\Admin\LocationController;
use Modules\Property\Http\Controllers\Admin\PropertyController;
use Modules\Property\Http\Controllers\Admin\PropertyTypeController;
use Modules\Property\Http\Controllers\Admin\TurkishCitizenshipController;

Route::middleware('can:Property Management')->group(function () {
    Route::delete('locations/deleteMulti', [LocationController::class, 'deleteMulti'])
        ->name('locations.deleteMulti');
    Route::resource('locations', LocationController::class)->except(['destroy', 'show']);

    Route::delete('property_types/deleteMulti', [PropertyTypeController::class, 'deleteMulti'])
        ->name('property_types.deleteMulti');
    Route::resource('property_types', PropertyTypeController::class)->except(['destroy', 'show']);

    Route::get('properties/location-children', [PropertyController::class, 'locationChildren'])
        ->name('properties.location_children');
    Route::delete('properties/deleteMulti', [PropertyController::class, 'deleteMulti'])
        ->name('properties.deleteMulti');
    Route::resource('properties', PropertyController::class)->except(['destroy', 'show']);

    Route::get('turkish-citizenship', [TurkishCitizenshipController::class, 'index'])
        ->name('turkish_citizenship.index');
    Route::post('turkish-citizenship', [TurkishCitizenshipController::class, 'store'])
        ->name('turkish_citizenship.store');
});
