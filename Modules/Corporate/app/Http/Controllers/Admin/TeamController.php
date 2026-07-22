<?php

namespace Modules\Corporate\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Cms\Application\Shared\Queries\ContentListQuery;
use Modules\Core\Http\Requests\DeleteMultiRequest;
use Modules\Core\Support\AdminImageInput;
use Modules\Corporate\Application\Team\Commands\UpsertTeamCommand;
use Modules\Corporate\Application\Team\TeamApplicationService;
use Modules\Corporate\Data\TeamData;
use Modules\Corporate\Models\Team;
use Modules\User\Enums\CmsStatus;

class TeamController extends Controller
{
    public function __construct(private readonly TeamApplicationService $teamService)
    {
        $this->setActive('corporate');
        $this->setActive('corporate_teams');
    }

    public function index()
    {
        $model = $this->teamService->paginate(new ContentListQuery(
            publish: request()->query('publish')
        ), [
            'id', 'name', 'avatar', 'position', 'link', 'rank', 'status', 'created_at',
        ]);

        return view('corporate::admin.team.index', compact('model'));
    }

    public function create()
    {
        return view('corporate::admin.team.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = TeamData::validate($this->preparePayload($request));

        $this->teamService->store(UpsertTeamCommand::fromValidated($data));

        return redirect()->route('admin.corporate_teams.index');
    }

    public function edit(Team $team)
    {
        return view('corporate::admin.team.edit', ['team' => $team]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $updateTranslations = $request->boolean('update_translations');

        $data = TeamData::validate($this->preparePayload($request));

        $this->teamService->update(
            $team,
            UpsertTeamCommand::fromValidated($data, $updateTranslations)
        );

        return redirect()->route('admin.corporate_teams.index');
    }

    public function deleteMulti(DeleteMultiRequest $request): RedirectResponse
    {
        $this->teamService->deleteMulti($request->input('ids'));

        return back();
    }

    /**
     * @return array<string, mixed>
     */
    private function preparePayload(Request $request): array
    {
        return [
            'name' => (string) $request->input('name'),
            'position' => $request->input('position') !== null && $request->input('position') !== ''
                ? (string) $request->input('position')
                : null,
            'link' => $request->filled('link') ? (string) $request->input('link') : null,
            'avatar' => AdminImageInput::resolveMediaPathOnly($request, 'img', 'img_media_path'),
            'rank' => (int) $request->input('rank', 0),
            'status' => $request->has('publish') ? CmsStatus::PUBLISHED : CmsStatus::ARCHIVED,
        ];
    }
}
