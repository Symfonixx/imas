<?php

namespace Modules\Property\Application\PropertyAttributeValue;

use Illuminate\Contracts\Filesystem\Filesystem;
use Throwable;

final class PropertyAttributeMediaChanges
{
    /**
     * @param  list<string>  $newPaths
     * @param  list<string>  $supersededPaths
     */
    public function __construct(
        private readonly Filesystem $disk,
        private readonly string $ownedPrefix,
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
        $this->deleteSafely($this->supersededPaths);
        $this->supersededPaths = [];
        $this->newPaths = [];
    }

    public function rollback(): void
    {
        $this->deleteSafely($this->newPaths);
        $this->newPaths = [];
        $this->supersededPaths = [];
    }

    private function isOwned(string $path): bool
    {
        return str_starts_with(ltrim($path, '/'), $this->ownedPrefix.'/');
    }

    /**
     * Cleanup is deliberately best-effort so one unavailable path does not
     * prevent the remaining owned files from being processed.
     *
     * @param  list<string>  $paths
     */
    private function deleteSafely(array $paths): void
    {
        foreach ($paths as $path) {
            if (! $this->isOwned($path)) {
                continue;
            }

            try {
                $this->disk->delete($path);
            } catch (Throwable) {
                // Continue cleaning the remaining owned paths.
            }
        }
    }
}
