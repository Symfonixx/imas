<?php

namespace Modules\Cms\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Cms\Models\Blog;
use Modules\Cms\Support\BlogCardSerializer;

class BlogController extends Controller
{
    /**
     * All published blogs as JSON (chatbot / external integrations).
     *
     * Accepts GET or POST. Optional query/body: locale, per_page (1–200), detail=1.
     */
    public function index(Request $request): JsonResponse
    {
        $this->applyLocale($request);

        $detail = $request->boolean('detail');
        $perPage = $request->integer('per_page', 0);
        $query = Blog::query()
            ->published()
            ->with(['category:id,name,slug'])
            ->latest();

        $map = static fn (Blog $blog) => $detail
            ? BlogCardSerializer::toDetailArray($blog)
            : BlogCardSerializer::toArray($blog);

        if ($perPage > 0) {
            $paginator = $query->paginate(min(max($perPage, 1), 200));

            return response()->json([
                'data' => $paginator->getCollection()->map($map)->values()->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        }

        $data = $query->get()->map($map)->values()->all();

        return response()->json([
            'data' => $data,
            'meta' => ['total' => count($data)],
        ]);
    }

    private function applyLocale(Request $request): void
    {
        $locale = $request->input('locale');
        $supported = array_keys(config('laravellocalization.supportedLocales', []));

        if (is_string($locale) && $locale !== '' && in_array($locale, $supported, true)) {
            app()->setLocale($locale);
        }
    }
}
