<?php

namespace Modules\Property\Application\Attribute;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Property\Application\Attribute\Commands\UpsertAttributeCommand;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Repositories\Attribute\AttributeRepository;

class AttributeApplicationService
{
    public function __construct(
        private readonly AttributeRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertAttributeCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'property_attributes',
            translatableFields: (new PropertyAttribute)->translatable,
            imageFields: [],
            updateTranslations: true
        );

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(PropertyAttribute $attribute, UpsertAttributeCommand $command): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'property_attributes',
            translatableFields: $attribute->translatable,
            imageFields: [],
            entity: $attribute,
            updateTranslations: $command->updateTranslations
        );

        $this->repository->update($payload, $attribute, $command->updateTranslations);
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
        cache()->forget('property_attributes');
    }
}
