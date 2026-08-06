<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\Base\Application\Seo\SeoDocumentService;

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
        $seoService = app(SeoDocumentService::class);

        return Inertia::render('User::Auth/ForgotPassword')
            ->withViewData([
                'seo' => $seoService->documentSeo([
                    'page_title' => $seoService->labelFromBaseLang('Forgot Password', 'Forgot Password'),
                    'robots' => 'noindex, nofollow',
                    'canonical' => route('password.request'),
                ]),
            ]);
    })->name('password.request');

    Route::get('/reset-password/{token}', function (Request $request, string $token) {
        $seoService = app(SeoDocumentService::class);

        return Inertia::render('User::Auth/ResetPassword', [
            'email' => (string) $request->query('email', ''),
            'token' => $token,
        ])->withViewData([
            'seo' => $seoService->documentSeo([
                'page_title' => $seoService->labelFromBaseLang('Reset Password', 'Reset Password'),
                'robots' => 'noindex, nofollow',
                'canonical' => url()->current(),
            ]),
        ]);
    })->name('password.reset');
});
