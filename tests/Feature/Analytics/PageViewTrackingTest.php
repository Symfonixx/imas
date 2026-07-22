<?php

namespace Tests\Feature\Analytics;

use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Modules\Base\Models\PageView;
use Tests\TestCase;

class PageViewTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_front_office_html_request_is_tracked_without_storing_the_ip_address(): void
    {
        $request = $this->request('/analytics-test-page', 'analytics.test.page', [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Analytics Test Browser',
        ]);

        app(TrackPageView::class)->handle(
            $request,
            fn () => response('Tracked page', 200, ['Content-Type' => 'text/html; charset=UTF-8'])
        );

        $pageView = PageView::query()->sole();

        $this->assertSame('/analytics-test-page', $pageView->path);
        $this->assertSame('analytics.test.page', $pageView->route_name);
        $this->assertSame(64, strlen($pageView->visitor_hash));
        $this->assertSame(now()->toDateString(), $pageView->viewed_on->toDateString());
        $this->assertStringNotContainsString('203.0.113.10', $pageView->visitor_hash);
    }

    public function test_admin_and_non_html_requests_are_not_tracked(): void
    {
        $adminRequest = $this->request('/admin/analytics-test-page', 'admin.analytics.test.page');
        app(TrackPageView::class)->handle(
            $adminRequest,
            fn () => response('Admin page', 200, ['Content-Type' => 'text/html; charset=UTF-8'])
        );

        $jsonRequest = $this->request('/analytics-test-json', 'analytics.test.json', [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        app(TrackPageView::class)->handle($jsonRequest, fn () => response()->json(['ok' => true]));

        $this->assertDatabaseCount('page_views', 0);
    }

    /**
     * @param  array<string, string>  $server
     */
    private function request(string $uri, string $routeName, array $server = []): Request
    {
        $request = Request::create($uri, 'GET', [], [], [], $server);
        $route = (new Route(['GET'], ltrim($uri, '/'), fn () => null))->name($routeName);
        $request->setRouteResolver(fn () => $route);

        return $request;
    }
}
