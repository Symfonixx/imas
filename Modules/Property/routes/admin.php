<?php

use Illuminate\Support\Facades\Route;
use Modules\Property\Http\Controllers\Admin\AttributeController;
use Modules\Property\Http\Controllers\Admin\AttributeFamilyController;
use Modules\Property\Http\Controllers\Admin\LocationController;
use Modules\Property\Http\Controllers\Admin\PropertyController;
use Modules\Property\Http\Controllers\Admin\TurkishCitizenshipController;
use Modules\Property\Http\Controllers\Admin\PropertyTypeController;

Route::middleware('can:Property Management')->group(function () {
    Route::delete('locations/deleteMulti', [LocationController::class, 'deleteMulti'])
        ->name('locations.deleteMulti');
    Route::resource('locations', LocationController::class)->except(['destroy', 'show']);

    Route::delete('attributes/deleteMulti', [AttributeController::class, 'deleteMulti'])
        ->name('attributes.deleteMulti');
    Route::resource('attributes', AttributeController::class)->except(['destroy', 'show']);

    Route::delete('attribute_families/deleteMulti', [AttributeFamilyController::class, 'deleteMulti'])
        ->name('attribute_families.deleteMulti');
    Route::resource('attribute_families', AttributeFamilyController::class)->except(['destroy', 'show']);

    Route::delete('property_types/deleteMulti', [PropertyTypeController::class, 'deleteMulti'])
        ->name('property_types.deleteMulti');
    Route::resource('property_types', PropertyTypeController::class)->except(['destroy', 'show']);

    Route::get('properties/attributes', [PropertyController::class, 'attributesByPropertyType'])
        ->name('properties.attributes');
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
