<?php

namespace Modules\User\Application\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Support\Models\ContactForm;
use Modules\Support\Models\Subscriber;

class DashboardAnalyticsService
{
    public const CACHE_KEY = 'admin.dashboard.analytics.v1';

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(5), fn (): array => $this->build());
    }

    /**
     * @return array<string, mixed>
     */
    private function build(): array
    {
        $today = Carbon::today()->toImmutable();
        $lastMonthStart = $today->subMonthNoOverflow()->startOfMonth();
        $thisMonthStart = $today->startOfMonth();
        $tomorrow = $today->addDay();
        $trendStart = $today->subDays(29);

        $viewerCounts = DB::table('page_views')
            ->selectRaw(
                'COUNT(DISTINCT visitor_hash) AS total_count,
                 COUNT(DISTINCT CASE WHEN viewed_on >= ? AND viewed_on < ? THEN visitor_hash END) AS last_month_count,
                 COUNT(DISTINCT CASE WHEN viewed_on >= ? AND viewed_on < ? THEN visitor_hash END) AS today_count',
                [
                    $lastMonthStart->toDateString(),
                    $thisMonthStart->toDateString(),
                    $today->toDateString(),
                    $tomorrow->toDateString(),
                ]
            )
            ->first();

        $trendCounts = DB::table('page_views')
            ->where('viewed_on', '>=', $trendStart->toDateString())
            ->where('viewed_on', '<', $tomorrow->toDateString())
            ->selectRaw('viewed_on, COUNT(DISTINCT visitor_hash) AS viewers')
            ->groupBy('viewed_on')
            ->pluck('viewers', 'viewed_on')
            ->mapWithKeys(static function (mixed $count, mixed $date): array {
                return [Carbon::parse((string) $date)->toDateString() => (int) $count];
            });

        $trendLabels = [];
        $trendData = [];
        for ($date = $trendStart; $date->lessThanOrEqualTo($today); $date = $date->addDay()) {
            $trendLabels[] = $date->format('M j');
            $trendData[] = (int) $trendCounts->get($date->toDateString(), 0);
        }

        $topPages = DB::table('page_views')
            ->selectRaw('path, route_name, COUNT(*) AS views, COUNT(DISTINCT visitor_hash) AS viewers')
            ->groupBy('path', 'route_name')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(static fn (object $row): array => [
                'path' => (string) $row->path,
                'route_name' => $row->route_name,
                'views' => (int) $row->views,
                'viewers' => (int) $row->viewers,
            ])
            ->all();

        $trafficSources = DB::table('page_views')
            ->selectRaw('referrer_host, COUNT(*) AS views, COUNT(DISTINCT visitor_hash) AS viewers')
            ->groupBy('referrer_host')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(static fn (object $row): array => [
                'source' => $row->referrer_host ?: 'Direct',
                'views' => (int) $row->views,
                'viewers' => (int) $row->viewers,
            ])
            ->all();

        return [
            'viewers' => [
                'total' => (int) ($viewerCounts->total_count ?? 0),
                'last_month' => (int) ($viewerCounts->last_month_count ?? 0),
                'today' => (int) ($viewerCounts->today_count ?? 0),
            ],
            'trend' => [
                'labels' => $trendLabels,
                'data' => $trendData,
            ],
            'top_pages' => $topPages,
            'traffic_sources' => $trafficSources,
            'contacts_total' => ContactForm::query()->count(),
            'subscribers_total' => Subscriber::query()->count(),
        ];
    }
}
