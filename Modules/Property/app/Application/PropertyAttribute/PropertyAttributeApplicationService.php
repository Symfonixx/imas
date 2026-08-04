<?php

namespace Modules\Property\Application\PropertyAttribute;

use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Core\Contracts\Translation\TranslatorInterface;
use Modules\Property\Application\PropertyAttribute\Commands\UpsertPropertyAttributeCommand;
use Modules\Property\Enums\AttributeType;
use Modules\Property\Models\PropertyAttribute;
use Modules\Property\Repositories\PropertyAttribute\PropertyAttributeRepository;

class PropertyAttributeApplicationService
{
    public function __construct(
        private readonly PropertyAttributeRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly TranslatorInterface $translator,
        private readonly FlashMessengerInterface $flashMessenger,
    ) {}

    public function paginate(array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns);
    }

    public function store(UpsertPropertyAttributeCommand $command): void
    {
        DB::transaction(function () use ($command): void {
            $attribute = $this->repository->create(
                $this->attributePayload($command, null, true)
            );
            $this->syncOptions($attribute, $command->options, true);
        });

        $this->flashMessenger->success();
    }

    public function update(
        PropertyAttribute $attribute,
        UpsertPropertyAttributeCommand $command
    ): void {
        DB::transaction(function () use ($attribute, $command): void {
            $newType = AttributeType::from((string) $command->attributes['type']);

            if ($attribute->type !== $newType && $this->repository->hasValues($attribute)) {
                throw ValidationException::withMessages([
                    'type' => __('The type cannot be changed after values have been saved.'),
                ]);
            }

            $this->repository->update(
                $attribute,
                $this->attributePayload($command, $attribute, $command->updateTranslations)
            );

            if ($newType->hasOptions()) {
                $this->syncOptions($attribute, $command->options, $command->updateTranslations);
            } else {
                $this->removeOptions($attribute, $attribute->options()->pluck('id')->all());
            }
        });

        $this->flashMessenger->success();
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    public function deleteMulti(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            if ($this->repository->anyHaveValues($ids)) {
                throw ValidationException::withMessages([
                    'ids' => __('An attribute with saved values cannot be deleted.'),
                ]);
            }

            $this->repository->deleteMany($ids);
        });

        $this->flashMessenger->success();
    }

    /**
     * @return array<string, mixed>
     */
    private function attributePayload(
        UpsertPropertyAttributeCommand $command,
        ?PropertyAttribute $attribute,
        bool $updateTranslations
    ): array {
        $payload = $this->payloadBuilder->build(
            data: $command->attributes,
            uploadPath: 'property-attributes',
            translatableFields: ['name', 'help_text'],
            imageFields: ['image'],
            existingMedia: [
                'image' => $attribute?->image,
            ],
            entity: $attribute,
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

        $payload['default_value'] = ($command->attributes['default_value'] ?? null) === null
            ? null
            : ['value' => $command->attributes['default_value']];

        return $payload;
    }

    /**
     * @param  list<array{id?: int, label: string, is_active?: bool}>  $submitted
     */
    private function syncOptions(
        PropertyAttribute $attribute,
        array $submitted,
        bool $updateTranslations
    ): void {
        $existing = $attribute->options()->get()->keyBy('id');
        $keptIds = [];
        $locale = app()->getLocale();

        foreach (array_values($submitted) as $position => $row) {
            $option = isset($row['id']) ? $existing->get((int) $row['id']) : null;

            if (isset($row['id']) && $option === null) {
                throw ValidationException::withMessages([
                    'options' => __('One or more submitted options are invalid.'),
                ]);
            }

            $label = trim((string) $row['label']);
            $labels = $option?->getTranslations('label') ?? [];
            $labels[$locale] = $label;

            if ($updateTranslations && $label !== '') {
                foreach ($this->translator->otherLanguages() as $language) {
                    try {
                        $labels[$language] = $this->translator->translate($language, $label);
                    } catch (Exception $exception) {
                        Log::error($exception->getMessage());
                    }
                }
            }

            $saved = $this->repository->saveOption($attribute, $option, [
                'label' => $labels,
                'position' => $position,
                'is_active' => (bool) ($row['is_active'] ?? false),
            ]);
            $keptIds[] = $saved->id;
        }

        $removedIds = $existing->keys()
            ->map(static fn ($id): int => (int) $id)
            ->diff($keptIds)
            ->values()
            ->all();

        $this->removeOptions($attribute, $removedIds);
    }

    /**
     * @param  list<int>  $ids
     */
    private function removeOptions(PropertyAttribute $attribute, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $referenced = array_intersect($ids, $this->repository->referencedOptionIds($attribute));

        if ($referenced !== []) {
            throw ValidationException::withMessages([
                'options' => __('An option with saved values cannot be removed.'),
            ]);
        }

        $this->repository->deleteOptions($attribute, $ids);
    }
}
