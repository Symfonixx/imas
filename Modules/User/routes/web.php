<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Front-office password reset (GET views)
|--------------------------------------------------------------------------
| Fortify POST routes stay registered with views=false. These GET routes keep
| named password.request / password.reset so reset emails resolve, and render
| thin Inertia shells that open AuthModal (Find Houses layout).
*/

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', function () {
        return Inertia::render('User::Auth/ForgotPassword');
    })->name('password.request');

    Route::get('/reset-password/{token}', function (Request $request, string $token) {
        return Inertia::render('User::Auth/ResetPassword', [
            'email' => (string) $request->query('email', ''),
            'token' => $token,
        ]);
    })->name('password.reset');
});
