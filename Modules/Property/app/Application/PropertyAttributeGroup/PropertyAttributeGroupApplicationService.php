<?php

namespace Modules\Property\Application\PropertyAttributeGroup;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Property\Application\PropertyAttributeGroup\Commands\UpsertPropertyAttributeGroupCommand;
use Modules\Property\Models\PropertyAttributeGroup;
use Modules\Property\Repositories\PropertyAttributeGroup\PropertyAttributeGroupRepository;

class PropertyAttributeGroupApplicationService
{
    public function __construct(
        private readonly PropertyAttributeGroupRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger,
    ) {}

    /**
     * @return Collection<int, PropertyAttributeGroup>
     */
    public function groups(): Collection
    {
        return $this->repository->allWithAttributes();
    }

    public function unassignedAttributes(): Collection
    {
        return $this->repository->unassignedAttributes();
    }

    public function store(UpsertPropertyAttributeGroupCommand $command): void
    {
        $this->repository->create($this->payload($command, null, true));
        $this->flashMessenger->success();
    }

    public function update(
        PropertyAttributeGroup $group,
        UpsertPropertyAttributeGroupCommand $command
    ): void {
        $this->repository->update(
            $group,
            $this->payload($command, $group, $command->updateTranslations)
        );
        $this->flashMessenger->success();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        $this->repository->deleteMany($ids);
        $this->flashMessenger->success();
    }

    /**
     * @param  list<array{id: int, attributes: list<int>}>  $groups
     */
    public function reorder(array $groups): void
    {
        DB::transaction(fn () => $this->repository->replaceOrdering($groups));
        $this->flashMessenger->success();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(
        UpsertPropertyAttributeGroupCommand $command,
        ?PropertyAttributeGroup $group,
        bool $updateTranslations
    ): array {
        $payload = $this->payloadBuilder->build(
            data: $command->attributes,
            uploadPath: 'property-attribute-groups',
            translatableFields: ['name'],
            imageFields: [],
            entity: $group,
            updateTranslations: $updateTranslations
        );

        unset(
            $payload['featured'],
            $payload['add_to_nav'],
            $payload['add_to_footer'],
            $payload['add_to_top_bar'],
            $payload['add_to_bottom_bar'],
            $payload['update_translations'],
        );

        return $payload;
    }
}
