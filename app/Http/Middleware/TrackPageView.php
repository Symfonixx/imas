<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Base\Models\PageView;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TrackPageView
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldTrack($request, $response)) {
            return $response;
        }

        try {
            PageView::query()->create([
                'path' => Str::limit('/'.ltrim($request->path(), '/'), 512, ''),
                'route_name' => $request->route()?->getName(),
                'referrer_host' => $this->referrerHost($request),
                'visitor_hash' => $this->visitorHash($request),
                'viewed_on' => now()->toDateString(),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        $routeName = (string) $request->route()?->getName();
        $userAgent = (string) $request->userAgent();
        $contentType = (string) $response->headers->get('Content-Type');

        return $request->isMethod('GET')
            && $response->isSuccessful()
            && str_contains(strtolower($contentType), 'text/html')
            && ! str_starts_with($routeName, 'admin.')
            && ! $request->is('admin/*', '*/admin/*')
            && ! preg_match('/bot|crawler|spider|slurp|preview/i', $userAgent);
    }

    private function visitorHash(Request $request): string
    {
        $identity = $request->user()
            ? 'user:'.$request->user()->getAuthIdentifier()
            : 'guest:'.($request->ip() ?? 'unknown').'|'.($request->userAgent() ?? 'unknown');

        return hash_hmac('sha256', $identity, (string) config('app.key'));
    }

    private function referrerHost(Request $request): ?string
    {
        $referrer = $request->headers->get('referer');
        if (! is_string($referrer) || $referrer === '') {
            return null;
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        $currentHost = $request->getHost();

        return is_string($host) && $host !== '' && ! hash_equals($currentHost, $host)
            ? strtolower($host)
            : null;
    }
}
