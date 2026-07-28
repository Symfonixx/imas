<?php

namespace App\Ssr;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Ssr\BundleDetector;
use Inertia\Ssr\HttpGateway;
use Inertia\Ssr\Response;
use Throwable;

class ImasHttpGateway extends HttpGateway
{
    private const CIRCUIT_CACHE_KEY = 'inertia.ssr.circuit_open';

    /**
     * @param  array<string, mixed>  $page
     */
    public function dispatch(array $page): ?Response
    {
        if (! config('inertia.ssr.enabled', true)) {
            return null;
        }

        if (
            config('inertia.ssr.ensure_bundle_exists', true)
            && (new BundleDetector)->detect() === null
        ) {
            return null;
        }

        if ($this->isCircuitOpen()) {
            return null;
        }

        try {
            $response = Http::timeout((int) config('inertia.ssr.timeout', 5))
                ->connectTimeout((int) config('inertia.ssr.connect_timeout', 1))
                ->post($this->getUrl('/render'), $page)
                ->throw()
                ->json();
        } catch (Exception $e) {
            if ($e instanceof StrayRequestException) {
                throw $e;
            }

            $this->openCircuit($e);

            return null;
        }

        if (! is_array($response)) {
            return null;
        }

        return new Response(
            implode("\n", $response['head'] ?? []),
            (string) ($response['body'] ?? ''),
        );
    }

    private function isCircuitOpen(): bool
    {
        try {
            return Cache::has(self::CIRCUIT_CACHE_KEY);
        } catch (Throwable) {
            return false;
        }
    }

    private function openCircuit(Exception $e): void
    {
        $ttl = max(30, (int) config('inertia.ssr.circuit_ttl', 300));
        $alreadyOpen = false;

        try {
            $alreadyOpen = Cache::has(self::CIRCUIT_CACHE_KEY);
            Cache::put(self::CIRCUIT_CACHE_KEY, true, $ttl);
        } catch (Throwable) {
            // Cache may be unavailable; still avoid flooding logs below.
        }

        if ($alreadyOpen) {
            return;
        }

        $level = $this->isConnectivityFailure($e) ? 'warning' : 'error';

        Log::{$level}('inertia.ssr.dispatch_failed', [
            'message' => $e->getMessage(),
            'circuit_ttl_seconds' => $ttl,
        ]);
    }

    private function isConnectivityFailure(Exception $e): bool
    {
        if ($e instanceof ConnectionException) {
            return true;
        }

        $message = strtolower($e->getMessage());

        return str_contains($message, 'curl error 7')
            || str_contains($message, 'failed to connect')
            || str_contains($message, 'connection refused');
    }
}
