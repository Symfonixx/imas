<?php

namespace App\Ssr;

use Exception;
use Illuminate\Http\Client\StrayRequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Ssr\BundleDetector;
use Inertia\Ssr\HttpGateway;
use Inertia\Ssr\Response;

class ImasHttpGateway extends HttpGateway
{
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

        try {
            $response = Http::timeout(120)
                ->post($this->getUrl('/render'), $page)
                ->throw()
                ->json();
        } catch (Exception $e) {
            if ($e instanceof StrayRequestException) {
                throw $e;
            }

            Log::warning('inertia.ssr.dispatch_failed', [
                'message' => $e->getMessage(),
            ]);

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
}
