<?php

namespace Modules\Property\Application\SlideCategory;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Property\Models\PropertySlideMedia;
use Modules\Property\Models\SlideCategory;
use Modules\Property\Repositories\SlideCategory\SlideCategoryRepository;

class SlideCategoryApplicationService
{
    public function __construct(
        private readonly SlideCategoryRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): void
    {
        $payload = $this->buildPayload($data, null, true);
        $this->repository->store($payload);

        $this->clearCache();
        $this->flashMessenger->success();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(
        SlideCategory $slideCategory,
        array $data,
        bool $updateTranslations = false
    ): void {
        $payload = $this->buildPayload($data, $slideCategory, $updateTranslations);
        $this->repository->update($payload, $slideCategory);

        $this->clearCache();
        $this->flashMessenger->success();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        $paths = PropertySlideMedia::query()
            ->whereIn('slide_category_id', $ids)
            ->pluck('path')
            ->filter()
            ->unique()
            ->values();
        $this->repository->deleteMulti($ids);

        foreach ($paths as $path) {
            if (PropertySlideMedia::isOwnedStoragePath($path)
                && ! PropertySlideMedia::query()->where('path', $path)->exists()
            ) {
                Storage::disk('public')->delete($path);
            }
        }

        $this->clearCache();
        $this->flashMessenger->success();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function buildPayload(
        array $data,
        ?SlideCategory $slideCategory,
        bool $updateTranslations
    ): array {
        $payload = $this->payloadBuilder->build(
            data: $data,
            uploadPath: 'property/slide-categories',
            translatableFields: (new SlideCategory)->translatable,
            imageFields: [],
            entity: $slideCategory,
            updateTranslations: $updateTranslations
        );

        unset(
            $payload['update_translations'],
            $payload['featured']
        );

        return $payload;
    }

    private function clearCache(): void
    {
        cache()->forget('slide_categories');
    }
}
