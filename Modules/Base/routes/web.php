<?php

use Illuminate\Support\Facades\Route;
use Modules\Base\Http\Controllers\AboutUsController;
use Modules\Base\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', AboutUsController::class)->name('about-us');
