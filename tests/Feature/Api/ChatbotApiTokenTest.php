<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Cms\Models\Blog;
use Modules\Property\Enums\LocationType;
use Modules\Property\Models\Location;
use Modules\Property\Models\Property;
use Modules\Property\Models\PropertyType;
use Modules\User\Enums\CmsStatus;
use Tests\TestCase;

class ChatbotApiTokenTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-chatbot-token-secret';

    protected function setUp(): void
    {
        parent::setUp();

        config(['api_tokens.tokens' => [self::TOKEN]]);
    }

    public function test_properties_endpoint_requires_token(): void
    {
        $this->getJson('/api/v1/properties')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthorized');
    }

    public function test_blogs_endpoint_requires_token(): void
    {
        $this->postJson('/api/v1/blogs')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthorized');
    }

    public function test_rejects_invalid_token(): void
    {
        $this->withToken('wrong-token')
            ->getJson('/api/v1/properties')
            ->assertUnauthorized();
    }

    public function test_returns_only_published_properties(): void
    {
        [$locationId, $typeId] = $this->seedPropertyDeps();

        $published = Property::factory()->published()->create([
            'location_id' => $locationId,
            'property_type_id' => $typeId,
            'title' => ['en' => 'Published Villa', 'ar' => 'فيلا منشورة', 'tr' => 'Yayinlanan Villa'],
            'price' => 250000,
        ]);
        Property::factory()->create([
            'location_id' => $locationId,
            'property_type_id' => $typeId,
            'status' => CmsStatus::ARCHIVED,
            'title' => ['en' => 'Archived Flat', 'ar' => 'شقة مؤرشفة', 'tr' => 'Arsiv Daire'],
        ]);

        $response = $this->withToken(self::TOKEN)
            ->postJson('/api/v1/properties');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonPath('data.0.price', '250000.00')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'title',
                    'price',
                    'url_key',
                    'thumbnail_url',
                    'location',
                    'property_type',
                ]],
                'meta' => ['total'],
            ]);

        $this->assertSame(1, count($response->json('data')));
    }

    public function test_returns_only_published_blogs(): void
    {
        $published = Blog::factory()->published()->create([
            'title' => ['en' => 'Market Tips', 'ar' => 'نصائح السوق'],
        ]);
        Blog::factory()->create([
            'status' => 'Archived',
            'title' => ['en' => 'Old Draft', 'ar' => 'مسودة قديمة'],
        ]);

        $response = $this->withToken(self::TOKEN)
            ->getJson('/api/v1/blogs');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.id', $published->id)
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'title',
                    'slug',
                    'excerpt',
                    'url',
                    'category',
                ]],
                'meta' => ['total'],
            ]);
    }

    public function test_accepts_x_api_token_header(): void
    {
        [$locationId, $typeId] = $this->seedPropertyDeps();
        Property::factory()->published()->create([
            'location_id' => $locationId,
            'property_type_id' => $typeId,
        ]);

        $this->withHeader('X-Api-Token', self::TOKEN)
            ->getJson('/api/v1/properties')
            ->assertOk()
            ->assertJsonPath('meta.total', 1);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function seedPropertyDeps(): array
    {
        $location = Location::query()->create([
            'name' => ['en' => 'Istanbul'],
            'type' => LocationType::City,
        ]);
        $type = PropertyType::factory()->create();

        return [(int) $location->id, (int) $type->id];
    }
}
