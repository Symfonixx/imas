<?php

namespace Modules\Corporate\Application\Testimonial;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Corporate\Application\Testimonial\Commands\UpsertTestimonialCommand;
use Modules\Corporate\Data\TestimonialData;
use Modules\Corporate\Models\Testimonial;
use Modules\Corporate\Repositories\Testimonial\TestimonialRepository;

class TestimonialApplicationService
{
    public function __construct(
        private readonly TestimonialRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertTestimonialCommand $command): void
    {
        $data = $this->normalisePayload($command->payload);
        $data['slug'] = Str::slug($data['client']) ?: uniqid('testimonial_', true);

        $payload = $this->payloadBuilder->build(
            data: $data,
            uploadPath: 'corporate_testimonials',
            translatableFields: (new Testimonial)->translatable,
            imageFields: ['avatar'],
            updateTranslations: true
        );
        unset($payload['featured']);

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(Testimonial $testimonial, UpsertTestimonialCommand $command): void
    {
        $data = $this->normalisePayload($command->payload);
        $data['slug'] = 'testimonial-'.$testimonial->id;

        $payload = $this->payloadBuilder->build(
            data: $data,
            uploadPath: 'corporate_testimonials',
            translatableFields: $testimonial->translatable,
            imageFields: ['avatar'],
            existingMedia: [
                'avatar' => $testimonial->avatar,
            ],
            entity: $testimonial,
            updateTranslations: $command->updateTranslations
        );
        unset($payload['featured']);

        $this->repository->update($payload, $testimonial, $command->updateTranslations);
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
     * @param  array<string, mixed>|TestimonialData  $payload
     * @return array<string, mixed>
     */
    private function normalisePayload(array|TestimonialData $payload): array
    {
        return $payload instanceof TestimonialData ? $payload->toArray() : $payload;
    }

    private function clearCache(): void
    {
        cache()->forget('corporate_testimonials');
    }
}
