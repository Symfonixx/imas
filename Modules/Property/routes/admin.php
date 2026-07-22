<?php

use Illuminate\Support\Facades\Route;
use Modules\Property\Http\Controllers\Admin\LocationController;
use Modules\Property\Http\Controllers\Admin\ProjectUnitTypeController;
use Modules\Property\Http\Controllers\Admin\PropertyAttributeController;
use Modules\Property\Http\Controllers\Admin\PropertyAttributeGroupController;
use Modules\Property\Http\Controllers\Admin\PropertyController;
use Modules\Property\Http\Controllers\Admin\PropertyTypeController;
use Modules\Property\Http\Controllers\Admin\SlideCategoryController;
use Modules\Property\Http\Controllers\Admin\TurkishCitizenshipController;

Route::middleware('can:Property Management')->group(function () {
    Route::delete('locations/deleteMulti', [LocationController::class, 'deleteMulti'])
        ->name('locations.deleteMulti');
    Route::resource('locations', LocationController::class)->except(['destroy', 'show']);

    Route::delete('property_types/deleteMulti', [PropertyTypeController::class, 'deleteMulti'])
        ->name('property_types.deleteMulti');
    Route::resource('property_types', PropertyTypeController::class)->except(['destroy', 'show']);

    Route::delete('project_unit_types/deleteMulti', [ProjectUnitTypeController::class, 'deleteMulti'])
        ->name('project_unit_types.deleteMulti');
    Route::resource('project_unit_types', ProjectUnitTypeController::class)->except(['destroy', 'show']);

    Route::delete('property_attributes/deleteMulti', [PropertyAttributeController::class, 'deleteMulti'])
        ->name('property_attributes.deleteMulti');
    Route::resource('property_attributes', PropertyAttributeController::class)->except(['destroy', 'show']);

    Route::match(['post', 'put'], 'property_attribute_groups/reorder', [PropertyAttributeGroupController::class, 'reorder'])
        ->name('property_attribute_groups.reorder');
    Route::delete('property_attribute_groups/deleteMulti', [PropertyAttributeGroupController::class, 'deleteMulti'])
        ->name('property_attribute_groups.deleteMulti');
    Route::resource('property_attribute_groups', PropertyAttributeGroupController::class)->except(['destroy', 'show']);

    Route::delete('slide_categories/deleteMulti', [SlideCategoryController::class, 'deleteMulti'])
        ->name('slide_categories.deleteMulti');
    Route::resource('slide_categories', SlideCategoryController::class)->except(['destroy', 'show']);

    Route::get('properties/location-children', [PropertyController::class, 'locationChildren'])
        ->name('properties.location_children');
    Route::get('properties/attribute-group-schema', [PropertyController::class, 'attributeGroupSchema'])
        ->name('properties.attribute_group_schema');
    Route::delete('properties/deleteMulti', [PropertyController::class, 'deleteMulti'])
        ->name('properties.deleteMulti');
    Route::resource('properties', PropertyController::class)->except(['destroy', 'show']);

    Route::get('turkish-citizenship', [TurkishCitizenshipController::class, 'index'])
        ->name('turkish_citizenship.index');
    Route::post('turkish-citizenship', [TurkishCitizenshipController::class, 'store'])
        ->name('turkish_citizenship.store');
});
