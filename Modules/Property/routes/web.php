<?php

use Illuminate\Support\Facades\Route;
use Modules\Property\Http\Controllers\PropertyController;
use Modules\Property\Http\Controllers\Property\PropertyController as PropertyPropertyController;

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

Route::resource('property', PropertyController::class)->names('property');

Route::get('/property', [PropertyPropertyController::class, 'index'])->name('property.index');