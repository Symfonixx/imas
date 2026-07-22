<?php

namespace Modules\Property\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Base\Application\Seo\SeoApplicationService;
use Modules\Base\Application\Settings\SettingsApplicationService;

class TurkishCitizenshipController extends Controller
{
    public function __construct(
        private readonly SettingsApplicationService $settingsService,
        private readonly SeoApplicationService $seoService
    ) {
        $this->setActive('cms');
        $this->setActive('turkish_citizenship');
    }

    public function index()
    {
        $settings = $this->settingsService->allKeyValue();
        $seo = $this->seoService->allKeyValue();

        return view('property::admin.turkish-citizenship.index', compact('settings', 'seo'));
    }

    public function store(Request $request)
    {
        $this->settingsService->update(
            images: [],
            mediaPaths: (array) $request->input('imgs_media', []),
            removedImageKeys: (array) $request->input('imgs_remove', [])
        );

        $this->seoService->update(
            data: $request->input('data', []),
            updateTranslations: $request->boolean('update_translations')
        );

        return back();
    }
}
