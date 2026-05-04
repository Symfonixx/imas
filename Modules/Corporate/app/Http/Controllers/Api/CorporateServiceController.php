<?php

namespace Modules\Corporate\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Corporate\Models\CorporateService;

class CorporateServiceController extends Controller
{
    /**
     * Published corporate services for the storefront.
     */
    public function index(): JsonResponse
    {
        $items = CorporateService::query()
            ->published()
            ->latest()
            ->get([
                'id', 'title', 'slug', 'description', 'content', 'image',
                'meta_title', 'meta_description', 'meta_keywords', 'meta_image',
                'featured', 'visits', 'created_at',
            ]);

        $data = $items->map(fn (CorporateService $row) => [
            'id' => $row->id,
            'title' => $row->title,
            'slug' => $row->slug,
            'description' => $row->description,
            'content' => $row->content,
            'image' => $row->image_link,
            'meta_title' => $row->meta_title,
            'meta_description' => $row->meta_description,
            'meta_keywords' => $row->meta_keywords,
            'meta_image' => $row->meta_image_link,
            'featured' => $row->featured,
            'visits' => $row->visits,
            'created_at' => $row->created_at,
        ]);

        return response()->json(['data' => $data]);
    }
}
