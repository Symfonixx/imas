<?php

namespace Modules\Base\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Base\Services\RssService;

class RssController extends Controller
{
    public function show(RssService $rssService, ?string $locale = null): Response
    {
        $locale = $locale ?? (string) config('app.locale', 'en');
        $supported = array_keys(config('laravellocalization.supportedLocales', []));

        if (! in_array($locale, $supported, true)) {
            abort(404);
        }

        return response()
            ->view('base::rss', [
                'feed' => $rssService->feed($locale),
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
