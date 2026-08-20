<?php

namespace Modules\Base\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Modules\Base\Application\Seo\SeoDocumentService;
use Symfony\Component\HttpFoundation\Response;

class NotFoundController
{
    public function __construct(private readonly SeoDocumentService $seo) {}

    public function __invoke(Request $request): Response
    {
        $pageTitle = $this->seo->labelFromBaseLang(
            'errors.not_found.title',
            'Page not found',
        );
        $description = $this->seo->labelFromBaseLang(
            'errors.not_found.message',
            'The page you are looking for could not be found.',
        );

        return Inertia::render('Errors/NotFound')
            ->withViewData([
                'seo' => $this->seo->documentSeo([
                    'page_title' => $pageTitle,
                    'description' => $description,
                    'robots' => 'noindex, nofollow',
                ]),
            ])
            ->toResponse($request)
            ->setStatusCode(404);
    }
}
