<?php

namespace Modules\Base\Application\Settings;

use Illuminate\Support\Collection;
use Modules\Base\Repositories\Settings\SettingsRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class SettingsApplicationService
{
    public function __construct(
        private readonly SettingsRepository $settingsRepository,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function allKeyValue(): Collection
    {
        return $this->settingsRepository->allKeyValue();
    }

    /**
     * @param  array<string, mixed>  $images  Unused for content settings (library-only); kept for call-site compatibility.
     * @param  array<string, mixed>  $data
     * @param  array<string, string|null>  $mediaPaths
     * @param  array<string, mixed>  $removedImageKeys  Keys from admin image-input remove (value "1"); clears setting without deleting files.
     */
    public function update(array $images = [], array $data = [], array $mediaPaths = [], array $removedImageKeys = []): void
    {
        foreach ($removedImageKeys as $key => $flag) {
            if ($flag === '1' || $flag === 1 || $flag === true) {
                $this->settingsRepository->set((string) $key, null);
            }
        }

        // Content settings images are library-only; ignore any uploaded files.
        unset($images);

        foreach ($mediaPaths as $key => $path) {
            if (is_string($path)) {
                $path = trim($path);
            }
            if (is_string($path) && $path !== '' && strcasecmp($path, 'null') !== 0) {
                $this->settingsRepository->set((string) $key, $path);
            }
        }

        foreach ($data as $key => $value) {
            $this->settingsRepository->set((string) $key, is_scalar($value) ? (string) $value : null);
        }

        cache()->forget('settings');
        $this->flashMessenger->success();
    }
}
