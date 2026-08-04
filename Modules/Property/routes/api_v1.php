<?php

use Illuminate\Support\Facades\Route;
use Modules\Property\Http\Controllers\Api\PropertyController;

/*
|--------------------------------------------------------------------------
| Token-protected Property API (chatbot / external)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1  Middleware: api + auth.api_token
|
*/

Route::match(['get', 'post'], 'properties', [PropertyController::class, 'index'])
    ->name('properties.index');
