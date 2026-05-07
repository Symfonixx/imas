<?php

namespace Modules\Property\Application\PropertyType;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Property\Application\PropertyType\Commands\UpsertPropertyTypeCommand;
use Modules\Property\Models\PropertyType;
use Modules\Property\Repositories\PropertyType\PropertyTypeRepository;

class PropertyTypeApplicationService
{
    public function __construct(
        private readonly PropertyTypeRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertPropertyTypeCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'property_types',
            translatableFields: (new PropertyType)->translatable,
            imageFields: [],
            updateTranslations: true
        );

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(PropertyType $propertyType, UpsertPropertyTypeCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'property_types',
            translatableFields: $propertyType->translatable,
            imageFields: [],
            entity: $propertyType,
            updateTranslations: $command->updateTranslations
        );

        $this->repository->update($payload, $propertyType, $command->updateTranslations);
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
        cache()->forget('property_types');
    }
}
