<?php

namespace Modules\User\Providers;

use Illuminate\Support\ServiceProvider;
// use Inertia\Inertia;
// use Laravel\Fortify\Fortify;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     */
    public function register(): void
    {
        // Login / register stay in AuthModal (Find Houses). Fortify views => false so
        // Fortify does not register GET login/register. Password-reset GET shells are
        // registered in Modules/User/routes/web.php and open AuthModal from those pages.
        // Fortify POST routes (password.email, password.update) remain for the modal.
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}
