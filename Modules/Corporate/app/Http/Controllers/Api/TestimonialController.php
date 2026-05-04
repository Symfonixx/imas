<?php

namespace Modules\Corporate\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Corporate\Models\Testimonial;

class TestimonialController extends Controller
{
    /**
     * Published testimonials for the storefront.
     */
    public function index(): JsonResponse
    {
        $items = Testimonial::query()
            ->published()
            ->orderBy('rank')
            ->orderByDesc('id')
            ->get([
                'id', 'name', 'client', 'avatar', 'position', 'link', 'quote', 'rank', 'status', 'created_at',
            ]);

        $data = $items->map(fn (Testimonial $row) => [
            'id' => $row->id,
            'name' => $row->name,
            'client' => $row->client,
            'avatar' => $row->avatar_link,
            'position' => $row->position,
            'link' => $row->link,
            'quote' => $row->quote,
            'rank' => $row->rank,
            'status' => $row->status,
            'created_at' => $row->created_at,
        ]);

        return response()->json(['data' => $data]);
    }
}
