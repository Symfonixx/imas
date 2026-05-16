<?php

namespace Modules\Property\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Modules\Property\Models\Property;
use Modules\Property\Models\UserFavoriteProperty;
use Modules\Property\Transformers\PropertyCardResource;
use Modules\User\Enums\CmsStatus;
use Modules\User\Models\User;

class FavoriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        $propertyCardWith = [
            'location:id,name',
            'propertyType:id,name,slug',
        ];

        $paginator = $user
            ->favoriteProperties()
            ->where('properties.status', CmsStatus::PUBLISHED)
            ->with($propertyCardWith)
            ->paginate(8)
            ->withQueryString();

        $paginator->through(function (Property $property) {
            $property->setAttribute('is_favorited', true);

            return $property;
        });

        return PropertyCardResource::collection($paginator);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = Validator::make($request->all(), [
            'property_id' => ['required', 'integer', 'exists:properties,id'],
        ])->validate();

        $propertyId = (int) $validated['property_id'];

        UserFavoriteProperty::query()->firstOrCreate([
            'user_id' => $user->id,
            'property_id' => $propertyId,
        ]);

        return response()->json([
            'favorited' => true,
            'property_id' => $propertyId,
        ]);
    }

    public function destroy(Request $request, Property $property): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        UserFavoriteProperty::query()
            ->where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->delete();

        return response()->json([
            'favorited' => false,
            'property_id' => $property->id,
        ]);
    }
}
