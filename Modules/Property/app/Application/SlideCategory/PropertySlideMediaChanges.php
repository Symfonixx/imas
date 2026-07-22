<?php

namespace Modules\Property\Application\SlideCategory;

use Illuminate\Contracts\Filesystem\Filesystem;
use Modules\Property\Models\PropertySlideMedia;
use Throwable;

final class PropertySlideMediaChanges
{
    /**
     * @param  list<string>  $newPaths
     * @param  list<string>  $supersededPaths
     */
    public function __construct(
        private readonly Filesystem $disk,
        private array $newPaths = [],
        private array $supersededPaths = [],
    ) {}

    public function trackNew(string $path): void
    {
        if ($this->isOwned($path) && ! in_array($path, $this->newPaths, true)) {
            $this->newPaths[] = $path;
        }
    }

    public function trackSuperseded(string $path): void
    {
        if ($this->isOwned($path) && ! in_array($path, $this->supersededPaths, true)) {
            $this->supersededPaths[] = $path;
        }
    }

    public function finalize(): void
    {
        $this->deleteSafely($this->supersededPaths, true);
        $this->newPaths = [];
        $this->supersededPaths = [];
    }

    public function rollback(): void
    {
        $this->deleteSafely($this->newPaths, false);
        $this->newPaths = [];
        $this->supersededPaths = [];
    }

    private function isOwned(string $path): bool
    {
        return PropertySlideMedia::isOwnedStoragePath($path);
    }

    /**
     * @param  list<string>  $paths
     */
    private function deleteSafely(array $paths, bool $onlyIfUnreferenced): void
    {
        foreach ($paths as $path) {
            if ($onlyIfUnreferenced
                && PropertySlideMedia::query()->where('path', $path)->exists()
            ) {
                continue;
            }

            try {
                $this->disk->delete($path);
            } catch (Throwable) {
                // Best-effort cleanup must not hide a successful database operation.
            }
        }
    }
}
