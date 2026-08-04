<?php

use Illuminate\Support\Facades\Route;
use Modules\Cms\Http\Controllers\Api\BlogController;

/*
|--------------------------------------------------------------------------
| Token-protected CMS API (chatbot / external)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1  Middleware: api + auth.api_token
|
*/

Route::match(['get', 'post'], 'blogs', [BlogController::class, 'index'])
    ->name('blogs.index');
