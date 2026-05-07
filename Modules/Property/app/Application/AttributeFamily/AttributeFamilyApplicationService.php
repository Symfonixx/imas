<?php

namespace Modules\Property\Application\AttributeFamily;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Property\Application\AttributeFamily\Commands\UpsertAttributeFamilyCommand;
use Modules\Property\Models\AttributeFamily;
use Modules\Property\Repositories\AttributeFamily\AttributeFamilyRepository;

class AttributeFamilyApplicationService
{
    public function __construct(
        private readonly AttributeFamilyRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    /**
     * @param  array<int, array{position: int}>  $sync  keyed by attribute id
     */
    public function store(UpsertAttributeFamilyCommand $command, array $sync): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'attribute_families',
            translatableFields: (new AttributeFamily)->translatable,
            imageFields: [],
            updateTranslations: true
        );

        $family = $this->repository->store($payload);
        if ($family instanceof AttributeFamily) {
            $family->attributes()->sync($sync);
        }

        $this->clearCache();
        $this->flashMessenger->success();
    }

    /**
     * @param  array<int, array{position: int}>  $sync
     */
    public function update(AttributeFamily $family, UpsertAttributeFamilyCommand $command, array $sync): void
    {
        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'attribute_families',
            translatableFields: $family->translatable,
            imageFields: [],
            entity: $family,
            updateTranslations: $command->updateTranslations
        );

        $this->repository->update($payload, $family, $command->updateTranslations);
        $family->attributes()->sync($sync);

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
        cache()->forget('property_attribute_families');
    }
}
