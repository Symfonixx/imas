<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Modules\Base\Repositories\Settings\SettingsRepository;

class AdminLayout extends Component
{
    protected \Illuminate\Contracts\Auth\Authenticatable|null|\App\Models\User $user;

    /**
     * Create a new component instance.
     */
    public function __construct(private readonly SettingsRepository $settingsRepository)
    {
        $this->user = auth()->user();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $adminLogoPath = trim((string) ($this->settingsRepository->get('admin_logo') ?? ''));
        $adminLogoUrl = $adminLogoPath !== ''
            ? asset('storage/'.$adminLogoPath)
            : asset('images/logo.png');

        return view('components.admin-layout', [
            'user' => $this->user,
            'adminLogoUrl' => $adminLogoUrl,
        ]);
    }
}
