<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Modules\Base\Models\PageView;
use Modules\Support\Models\ContactForm;
use Modules\Support\Models\Subscriber;
use Tests\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_analytics_widgets(): void
    {
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleCookieRedirect::class,
            LocaleSessionRedirect::class,
        ]);

        PageView::query()->create([
            'path' => '/home',
            'route_name' => 'home',
            'referrer_host' => 'google.com',
            'visitor_hash' => 'visitor-a',
            'viewed_on' => now()->toDateString(),
            'created_at' => now(),
        ]);

        ContactForm::query()->create([
            'ip_address' => '127.0.0.1',
            'name' => 'Dashboard Contact',
            'email' => 'contact@example.test',
            'message' => 'Analytics inquiry',
        ]);

        Subscriber::query()->create([
            'ip_address' => '127.0.0.1',
            'email' => 'subscriber@example.test',
            'lang' => 'en',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.dashboard.index'))
            ->assertOk()
            ->assertSee('Total Viewers', false)
            ->assertSee('Last Month Viewers', false)
            ->assertSee('Today Viewers', false)
            ->assertSee('Viewers Trend', false)
            ->assertSee('Top 10 Most Visited Pages', false)
            ->assertSee('Top 10 Traffic Sources', false)
            ->assertSee('Total Contacts', false)
            ->assertSee('Total Subscribers', false)
            ->assertSee('/home', false)
            ->assertSee('google.com', false);
    }

    private function admin(): User
    {
        return User::query()->create([
            'name' => 'Dashboard Admin',
            'email' => Str::uuid().'@example.test',
            'mobile' => (string) random_int(1000000000, 9999999999),
            'password' => 'password',
            'type' => 'admin',
            'img' => null,
        ]);
    }
}
