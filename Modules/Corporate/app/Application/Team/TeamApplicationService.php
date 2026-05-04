<?php

namespace Modules\Corporate\Application\Team;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Cms\Application\Shared\Support\ContentPayloadBuilder;
use Modules\Core\Contracts\Flash\FlashMessengerInterface;
use Modules\Corporate\Application\Team\Commands\UpsertTeamCommand;
use Modules\Corporate\Data\TeamData;
use Modules\Corporate\Models\Team;
use Modules\Corporate\Repositories\Team\TeamRepository;

class TeamApplicationService
{
    public function __construct(
        private readonly TeamRepository $repository,
        private readonly ContentPayloadBuilder $payloadBuilder,
        private readonly FlashMessengerInterface $flashMessenger
    ) {}

    public function paginate(ContentListQuery $query, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->all($columns, $query->toArray());
    }

    public function store(UpsertTeamCommand $command): void
    {
        $data = $this->normalisePayload($command->payload);
        $data['slug'] = Str::slug(Str::limit($data['name'] ?? '', 80, '')) ?: uniqid('team_', true);

        $payload = $this->payloadBuilder->build(
            data: $data,
            uploadPath: 'corporate_teams',
            translatableFields: (new Team)->translatable,
            imageFields: ['avatar'],
            updateTranslations: true
        );
        unset($payload['featured']);

        $this->repository->store($payload);
        $this->clearCache();
        $this->flashMessenger->success();
    }

    public function update(Team $team, UpsertTeamCommand $command): void
    {
        $data = $this->normalisePayload($command->payload);
        $data['slug'] = 'team-'.$team->id;

        $payload = $this->payloadBuilder->build(
            data: $data,
            uploadPath: 'corporate_teams',
            translatableFields: $team->translatable,
            imageFields: ['avatar'],
            existingMedia: [
                'avatar' => $team->avatar,
            ],
            entity: $team,
            updateTranslations: $command->updateTranslations
        );
        unset($payload['featured']);

        $this->repository->update($payload, $team, $command->updateTranslations);
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
     * @param  array<string, mixed>|TeamData  $payload
     * @return array<string, mixed>
     */
    private function normalisePayload(array|TeamData $payload): array
    {
        return $payload instanceof TeamData ? $payload->toArray() : $payload;
    }

    private function clearCache(): void
    {
        cache()->forget('corporate_teams');
    }
}
