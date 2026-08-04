<?php

namespace Modules\Property\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Property\Models\Property;
use Modules\Property\Support\PropertyCardEagerLoads;
use Modules\Property\Support\PropertyListingCardSerializer;
use Modules\User\Enums\CmsStatus;

class PropertyController extends Controller
{
    /**
     * All published properties as JSON (chatbot / external integrations).
     *
     * Accepts GET or POST. Optional query/body: locale, per_page (1–200).
     */
    public function index(Request $request): JsonResponse
    {
        $this->applyLocale($request);

        $perPage = $request->integer('per_page', 0);
        $query = Property::query()
            ->where('status', CmsStatus::PUBLISHED)
            ->with(PropertyCardEagerLoads::relations())
            ->latest('updated_at');

        if ($perPage > 0) {
            $paginator = $query->paginate(min(max($perPage, 1), 200));

            return response()->json([
                'data' => $paginator->getCollection()
                    ->map(static fn (Property $property) => PropertyListingCardSerializer::toArray($property))
                    ->values()
                    ->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        }

        $data = $query->get()
            ->map(static fn (Property $property) => PropertyListingCardSerializer::toArray($property))
            ->values()
            ->all();

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
