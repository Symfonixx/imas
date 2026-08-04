<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Base\Support\FrontSeo;
use Modules\Base\Support\FrontViewData;

/*
|--------------------------------------------------------------------------
| Front-office password reset (GET views)
|--------------------------------------------------------------------------
| Fortify POST routes stay registered with views=false. These GET routes keep
| named password.request / password.reset so reset emails resolve, and render
| Blade shells that open AuthModal (Find Houses layout).
*/

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', function (FrontViewData $frontViewData) {
        $globals = $frontViewData->sharedGlobals();
        $localeSwitcher = $frontViewData->getLocaleSwitcher();
        $appName = $frontViewData->sharedAppName();
        $translations = $frontViewData->getTranslations();

        return view('user::front.auth.forgot-password', [
            'seo' => FrontSeo::make([
                'title' => front_trans('Forgot Password', $translations).' | '.$appName,
                'robots' => 'noindex, nofollow',
                'canonical' => route('password.request'),
            ], $globals, $localeSwitcher, $appName),
        ]);
    })->name('password.request');

    Route::get('/reset-password/{token}', function (Request $request, string $token, FrontViewData $frontViewData) {
        $globals = $frontViewData->sharedGlobals();
        $localeSwitcher = $frontViewData->getLocaleSwitcher();
        $appName = $frontViewData->sharedAppName();
        $translations = $frontViewData->getTranslations();

        return view('user::front.auth.reset-password', [
            'email' => (string) $request->query('email', ''),
            'token' => $token,
            'reset_token' => $token,
            'reset_email' => (string) $request->query('email', ''),
            'seo' => FrontSeo::make([
                'title' => front_trans('Reset Password', $translations).' | '.$appName,
                'robots' => 'noindex, nofollow',
                'canonical' => url()->current(),
            ], $globals, $localeSwitcher, $appName),
        ]);
    })->name('password.reset');
});
