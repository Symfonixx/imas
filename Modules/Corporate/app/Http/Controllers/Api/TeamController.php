<?php

namespace Modules\Corporate\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Corporate\Models\Team;

class TeamController extends Controller
{
    /**
     * Published team members for the storefront.
     */
    public function index(): JsonResponse
    {
        $items = Team::query()
            ->published()
            ->orderBy('rank')
            ->orderByDesc('id')
            ->get([
                'id', 'name', 'avatar', 'position', 'link', 'rank', 'status', 'created_at',
            ]);

        $data = $items->map(fn (Team $row) => [
            'id' => $row->id,
            'name' => $row->name,
            'avatar' => $row->avatar_link,
            'position' => $row->position,
            'link' => $row->link,
            'rank' => $row->rank,
            'status' => $row->status,
            'created_at' => $row->created_at,
        ]);

        return response()->json(['data' => $data]);
    }
}
