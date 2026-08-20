<?php

use Illuminate\Support\Facades\Route;
use Modules\Property\Http\Controllers\Property\PropertyController as PropertyPropertyController;
use Modules\Property\Http\Controllers\PropertyController;
use Modules\Property\Http\Controllers\TurkishCitizenshipController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::resource('property', PropertyController::class)->except(['index', 'show'])->names('property');

Route::middleware('auth')->group(function () {
    Route::get('/favorite-properties', [PropertyPropertyController::class, 'favoriteProperties'])
        ->name('property.favorites');
});

Route::get('/property', [PropertyPropertyController::class, 'index'])->name('property.index');
Route::get('/property/{property}', [PropertyPropertyController::class, 'show'])->name('property.show');

Route::get('/turkish-citizenship', TurkishCitizenshipController::class)
    ->name('turkish-citizenship');
