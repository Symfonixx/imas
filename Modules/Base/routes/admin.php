<?php

use Illuminate\Support\Facades\Route;
use Modules\Base\Http\Controllers\Admin\AboutUsController;
use Modules\Base\Http\Controllers\Admin\LogController;
use Modules\Base\Http\Controllers\Admin\MediaLibraryController;
use Modules\Base\Http\Controllers\Admin\SeoController;
use Modules\Base\Http\Controllers\Admin\SettingsController;

// Group for Settings Management
Route::middleware('can:Settings Management')->group(function () {
    Route::resource('settings', SettingsController::class)->only(['index', 'store']);
    Route::resource('seo', SeoController::class)->only(['index', 'store']);
    Route::get('about-us', [AboutUsController::class, 'index'])->name('about_us.index');
    Route::post('about-us', [AboutUsController::class, 'store'])->name('about_us.store');
});

Route::middleware('can:Media Library Management')->group(function () {
    Route::get('media-library/list', [MediaLibraryController::class, 'list'])->name('media_library.list');
    Route::get('media-library/resolve', [MediaLibraryController::class, 'resolve'])->name('media_library.resolve');
    Route::get('media-library/folders', [MediaLibraryController::class, 'folders'])->name('media_library.folders.index');
    Route::post('media-library/folders', [MediaLibraryController::class, 'storeFolder'])->name('media_library.folders.store');
    Route::patch('media-library/folders/{folder}', [MediaLibraryController::class, 'updateFolder'])->name('media_library.folders.update');
    Route::delete('media-library/folders/{folder}', [MediaLibraryController::class, 'destroyFolder'])->name('media_library.folders.destroy');
    Route::post('media-library', [MediaLibraryController::class, 'store'])->name('media_library.store');
    Route::delete('media-library/delete-multi', [MediaLibraryController::class, 'deleteMulti'])->name('media_library.delete_multi');
    Route::patch('media-library/{media}', [MediaLibraryController::class, 'update'])->name('media_library.update');
    Route::delete('media-library/{media}', [MediaLibraryController::class, 'destroy'])->name('media_library.destroy');
    Route::get('media-library', [MediaLibraryController::class, 'index'])->name('media_library.index');
});

// Group for Logs Management
Route::middleware('can:Logs Management')->group(function () {
    Route::delete('logs/deleteMulti', [LogController::class, 'deleteMulti'])->name('logs.deleteMulti');
    Route::resource('logs', LogController::class)->only(['index', 'show']);
});
