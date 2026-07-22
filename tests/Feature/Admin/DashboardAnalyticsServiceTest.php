<?php

namespace Tests\Feature\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Base\Models\PageView;
use Modules\Support\Models\ContactForm;
use Modules\Support\Models\Subscriber;
use Modules\User\Application\Dashboard\DashboardAnalyticsService;
use Tests\TestCase;

class DashboardAnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_it_returns_cached_viewer_trends_rankings_and_lead_totals(): void
    {
        Carbon::setTestNow('2026-07-21 12:00:00');
        Cache::flush();

        $this->recordView('/home', 'home', 'visitor-a', '2026-07-21', 'google.com');
        $this->recordView('/home', 'home', 'visitor-a', '2026-07-21', 'google.com');
        $this->recordView('/properties', 'property.index', 'visitor-b', '2026-07-21');
        $this->recordView('/home', 'home', 'visitor-a', '2026-06-15', 'google.com');
        $this->recordView('/properties', 'property.index', 'visitor-c', '2026-06-20', 'facebook.com');
        $this->recordView('/about', 'page.show', 'visitor-d', '2026-05-01');

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

        $analytics = app(DashboardAnalyticsService::class)->get();

        $this->assertSame(4, $analytics['viewers']['total']);
        $this->assertSame(2, $analytics['viewers']['last_month']);
        $this->assertSame(2, $analytics['viewers']['today']);
        $this->assertCount(30, $analytics['trend']['labels']);
        $this->assertSame(2, $analytics['trend']['data'][29]);
        $this->assertSame('/home', $analytics['top_pages'][0]['path']);
        $this->assertSame(3, $analytics['top_pages'][0]['views']);
        $this->assertSame('google.com', $analytics['traffic_sources'][0]['source']);
        $this->assertSame(3, $analytics['traffic_sources'][0]['views']);
        $this->assertSame(1, $analytics['contacts_total']);
        $this->assertSame(1, $analytics['subscribers_total']);

        PageView::query()->create([
            'path' => '/cached',
            'visitor_hash' => 'visitor-e',
            'viewed_on' => now()->toDateString(),
            'created_at' => now(),
        ]);

        $this->assertSame(4, app(DashboardAnalyticsService::class)->get()['viewers']['total']);
    }

    private function recordView(
        string $path,
        ?string $routeName,
        string $visitorHash,
        string $viewedOn,
        ?string $referrerHost = null
    ): void {
        PageView::query()->create([
            'path' => $path,
            'route_name' => $routeName,
            'referrer_host' => $referrerHost,
            'visitor_hash' => $visitorHash,
            'viewed_on' => $viewedOn,
            'created_at' => $viewedOn.' 12:00:00',
        ]);
    }
}
