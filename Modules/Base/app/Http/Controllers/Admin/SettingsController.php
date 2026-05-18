<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Modules\Base\Application\Settings\SettingsApplicationService;
use Modules\Base\Http\Requests\StoreSettingsRequest;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsApplicationService $settingsService)
    {
        $this->setActive('settings');
    }

    public function index()
    {
        $this->setActive('websiteConfigurations');
        $settings = $this->settingsService->allKeyValue();

        return view('base::admin.settings.index', compact('settings'));
    }

    public function store(StoreSettingsRequest $request)
    {
        $mediaPaths = (array) $request->input('imgs_media', []);
        $removed = (array) $request->input('imgs_remove', []);
        $this->settingsService->update(
            $request->file('imgs', []),
            $request->input('data', []),
            $mediaPaths,
            $removed
        );

        return back();
    }
}
