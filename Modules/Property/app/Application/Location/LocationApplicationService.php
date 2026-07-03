<?php

namespace Modules\Property\Application\Location;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Property\Application\Location\Commands\UpsertLocationCommand;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Repositories\Location\LocationRepository;

class LocationApplicationService
{
    public function __construct(
        private readonly LocationRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertLocationCommand $command): void
    {
        $this->assertUniqueSibling(null, $command->payload);
        $this->assertParentNotCyclic(null, $command->payload['parent_id'] ?? null);

        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'locations',
            translatableFields: (new Location)->translatable,
            imageFields: [],
            updateTranslations: true
        );

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(Location $location, UpsertLocationCommand $command): void
    {
        $this->assertUniqueSibling($location, $command->payload);
        $this->assertParentNotCyclic($location, $command->payload['parent_id'] ?? null);

        $payload = $this->payloadBuilder->build(
            data: $command->payload,
            uploadPath: 'locations',
            translatableFields: $location->translatable,
            imageFields: [],
            entity: $location,
            updateTranslations: $command->updateTranslations
        );

        $this->repository->update($payload, $location, $command->updateTranslations);
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

    /**
     * @param  array<string, mixed>  $payload
     */
    private function assertUniqueSibling(?Location $location, array $payload): void
    {
        $name = trim((string) ($payload['name'] ?? ''));
        if ($name === '') {
            return;
        }

        $normalizedName = mb_strtolower($name);
        $type = (string) ($payload['type'] ?? '');
        $parentId = isset($payload['parent_id']) ? (int) $payload['parent_id'] : null;

        $query = Location::query()->where('type', $type);

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        if ($location !== null) {
            $query->whereKeyNot($location->id);
        }

        foreach ($query->get(['id', 'name']) as $sibling) {
            foreach ($sibling->getTranslations('name') as $translation) {
                $candidate = trim((string) $translation);
                if ($candidate !== '' && mb_strtolower($candidate) === $normalizedName) {
                    throw ValidationException::withMessages([
                        'name' => $this->duplicateLocationNameMessage($type),
                    ]);
                }
            }
        }
    }

    private function duplicateLocationNameMessage(string $type): string
    {
        return match ($type) {
            LocationType::City->value => __('A city with this name already exists.'),
            LocationType::Municipality->value => __('A municipality with this name already exists in this city.'),
            LocationType::Area->value => __('An area with this name already exists in this municipality.'),
            default => __('A location with this name already exists at this level.'),
        };
    }

    private function assertParentNotCyclic(?Location $location, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($location !== null && $parentId === $location->id) {
            throw ValidationException::withMessages([
                'parent_id' => __('Invalid parent selection.'),
            ]);
        }

        if ($location === null) {
            return;
        }

        $blocked = array_merge([$location->id], Location::descendantIdsOf($location->id));
        if (in_array($parentId, $blocked, true)) {
            throw ValidationException::withMessages([
                'parent_id' => __('A location cannot be its own parent or descendant.'),
            ]);
        }
    }

    private function clearCache(): void
    {
        cache()->forget('property_locations');
    }
}
