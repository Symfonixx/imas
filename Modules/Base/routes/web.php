<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Base\Http\Controllers\AboutUsController;
use Modules\Base\Http\Controllers\HomeController;
use Modules\Base\Http\Controllers\NotFoundController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about-us', AboutUsController::class)->name('about-us');

/*
| Front-office 404 (localized web stack so Inertia shared props still load).
| Admin / API unmatched paths are left to Laravel’s default handler.
*/
Route::fallback(function (Request $request, NotFoundController $notFound) {
    if (preg_match('#(^|/)admin(/|$)#', $request->path()) === 1) {
        abort(404);
    }

    return $notFound($request);
})->name('fallback');
