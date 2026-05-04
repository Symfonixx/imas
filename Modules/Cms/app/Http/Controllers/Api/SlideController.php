<?php

namespace Modules\Cms\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Cms\Models\Slide;

class SlideController extends Controller
{
    /**
     * Published slides for the storefront, ordered by rank.
     */
    public function index(): JsonResponse
    {
        $slides = Slide::query()
            ->published()
            ->orderBy('rank')
            ->get(['id', 'image', 'main_title', 'subtitle', 'link', 'rank']);

        $data = $slides->map(fn (Slide $slide) => [
            'id' => $slide->id,
            'image' => $slide->image_link,
            'main_title' => $slide->main_title,
            'subtitle' => $slide->subtitle,
            'link' => $slide->link,
            'rank' => $slide->rank,
        ]);

        return response()->json(['data' => $data]);
    }
}
