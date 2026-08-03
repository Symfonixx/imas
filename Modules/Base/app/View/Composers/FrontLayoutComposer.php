<?php

namespace Modules\Base\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Modules\Base\Support\FrontViewData;

class FrontLayoutComposer
{
    public function __construct(private readonly FrontViewData $frontViewData) {}

    public function compose(View $view): void
    {
        $data = $this->frontViewData->forRequest(request());

        // Page-level seo / navbar_transparent already set on the view win.
        $existing = $view->getData();
        if (isset($existing['seo']) && is_array($existing['seo'])) {
            unset($data['seo']);
        }
        if (array_key_exists('navbar_transparent', $existing)) {
            unset($data['navbar_transparent']);
        }

        ViewFacade::share('translations', $data['translations'] ?? []);

        $view->with($data);
    }
}
