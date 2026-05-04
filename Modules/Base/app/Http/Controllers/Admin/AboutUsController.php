<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Base\Application\Seo\SeoApplicationService;
use Modules\Base\Application\Settings\SettingsApplicationService;

class AboutUsController extends Controller
{
    public function __construct(
        private readonly SettingsApplicationService $settingsService,
        private readonly SeoApplicationService $seoService
    ) {
        $this->setActive('settings');
    }

    public function index()
    {
        $this->setActive('about_us');
        $settings = $this->settingsService->allKeyValue();
        $seo = $this->seoService->allKeyValue();

        return view('base::admin.about-us.index', compact('settings', 'seo'));
    }

    public function store(Request $request)
    {
        $this->settingsService->update(
            images: $request->file('imgs', []),
            mediaPaths: (array) $request->input('imgs_media', [])
        );

        $this->seoService->update(
            data: $request->input('data', []),
            updateTranslations: $request->boolean('update_translations')
        );

        return back();
    }
}
