<?php

namespace Modules\Cms\Application\Slide;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Cms\Application\Slide\Commands\UpsertSlideCommand;
use Modules\Cms\Models\Slide;
use Modules\Cms\Repositories\Slide\SlideRepository;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class SlideApplicationService
{
    public function __construct(
        private readonly SlideRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertSlideCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'slides',
            translatableFields: (new Slide)->translatable,
            imageFields: ['image'],
            updateTranslations: true
        );

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(Slide $slide, UpsertSlideCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'slides',
            translatableFields: $slide->translatable,
            imageFields: ['image'],
            existingMedia: [
                'image' => $slide->image,
            ],
            entity: $slide,
            updateTranslations: $command->updateTranslations
        );

        $this->repository->update($payload, $slide, $command->updateTranslations);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        $this->repository->deleteMulti($ids);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    private function clearCache(): void
    {
        cache()->forget('slides');
    }
}
