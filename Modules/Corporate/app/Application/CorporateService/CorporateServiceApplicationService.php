<?php

namespace Modules\Corporate\Application\CorporateService;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Corporate\Application\CorporateService\Commands\UpsertCorporateServiceCommand;
use Modules\Corporate\Models\CorporateService;
use Modules\Corporate\Repositories\CorporateService\CorporateServiceRepository;

class CorporateServiceApplicationService
{
    public function __construct(
        private readonly CorporateServiceRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertCorporateServiceCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'corporate_services',
            translatableFields: (new CorporateService)->translatable,
            imageFields: ['image', 'meta_image'],
            updateTranslations: true
        );

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(CorporateService $corporateService, UpsertCorporateServiceCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'corporate_services',
            translatableFields: $corporateService->translatable,
            imageFields: ['image', 'meta_image'],
            existingMedia: [
                'image' => $corporateService->image,
                'meta_image' => $corporateService->meta_image,
            ],
            entity: $corporateService,
            updateTranslations: $command->updateTranslations
        );

        $this->repository->update($payload, $corporateService, $command->updateTranslations);
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
        cache()->forget('corporate_services');
    }
}
