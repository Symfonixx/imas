<?php

namespace Modules\Base\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Base\Services\SitemapService;
use Modules\Base\Support\SitemapXmlRenderer;

class SitemapController extends Controller
{
    public function __invoke(SitemapService $sitemapService, SitemapXmlRenderer $renderer): Response
    {
        return response(
            $renderer->render($sitemapService->entries()),
            200,
            ['Content-Type' => 'text/xml; charset=UTF-8'],
        );
    }
}
