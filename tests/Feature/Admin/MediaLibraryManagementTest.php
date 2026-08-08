<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter;
use Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Modules\Base\Models\Media;
use Modules\Base\Models\MediaFolder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MediaLibraryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_admin_can_create_a_folder_and_upload_an_image_with_metadata(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $folderResponse = $this->postJson(route('admin.media_library.folders.store'), [
            'name' => 'Property Exteriors',
        ]);

        $folderResponse
            ->assertCreated()
            ->assertJsonPath('folder.name', 'Property Exteriors');

        $folder = MediaFolder::query()->firstOrFail();

        $uploadResponse = $this->postJson(route('admin.media_library.store'), [
            'files' => [UploadedFile::fake()->image('villa.jpg', 1200, 800)],
            'folder_id' => $folder->id,
            'alt_text' => 'Villa overlooking the sea',
            'title' => 'Seaside villa',
            'caption' => 'A featured property in Antalya.',
        ]);

        $uploadResponse
            ->assertOk()
            ->assertJsonPath('items.0.folder_id', $folder->id)
            ->assertJsonPath('items.0.alt_text', 'Villa overlooking the sea');

        $media = Media::query()->firstOrFail();

        $this->assertSame($folder->id, $media->folder_id);
        $this->assertSame('Villa overlooking the sea', $media->alt_text);
        $this->assertSame('Seaside villa', $media->title);
        $this->assertSame('A featured property in Antalya.', $media->caption);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_admin_can_filter_media_by_folder_and_update_image_metadata(): void
    {
        $this->actingAs($this->admin());
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleCookieRedirect::class,
            LocaleSessionRedirect::class,
        ]);
        $folder = MediaFolder::query()->create([
            'name' => 'Interiors',
            'slug' => 'interiors',
            'user_id' => auth()->id(),
        ]);
        $inside = Media::query()->create($this->mediaAttributes([
            'folder_id' => $folder->id,
            'path' => 'media-library/interiors/inside.jpg',
        ]));
        Media::query()->create($this->mediaAttributes([
            'path' => 'media-library/root.jpg',
        ]));

        $this->getJson(route('admin.media_library.list', ['folder_id' => $folder->id]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $inside->id);

        $this->patchJson(route('admin.media_library.update', $inside), [
            'name' => 'Living room hero',
            'alt_text' => 'Updated alternative text',
            'title' => 'Updated title',
            'caption' => 'Updated caption',
        ])->assertOk();

        $this->assertDatabaseHas('media', [
            'id' => $inside->id,
            'name' => 'Living room hero',
            'alt_text' => 'Updated alternative text',
            'title' => 'Updated title',
            'caption' => 'Updated caption',
        ]);
    }

    public function test_admin_can_rename_a_folder_without_changing_storage_slug(): void
    {
        $this->actingAs($this->admin());
        $folder = MediaFolder::query()->create([
            'name' => 'Campaign A',
            'slug' => 'campaign-a',
            'user_id' => auth()->id(),
        ]);

        $this->patchJson(route('admin.media_library.folders.update', $folder), [
            'name' => 'Campaign B',
        ])
            ->assertOk()
            ->assertJsonPath('folder.name', 'Campaign B');

        $this->assertDatabaseHas('media_folders', [
            'id' => $folder->id,
            'name' => 'Campaign B',
            'slug' => 'campaign-a',
        ]);
    }

    public function test_admin_can_create_nested_subfolders_and_list_parent_ids(): void
    {
        $this->actingAs($this->admin());
        $this->withoutMiddleware([
            LaravelLocalizationRedirectFilter::class,
            LocaleCookieRedirect::class,
            LocaleSessionRedirect::class,
        ]);

        $parentResponse = $this->postJson(route('admin.media_library.folders.store'), [
            'name' => 'Properties',
        ]);
        $parentResponse->assertCreated();
        $parentId = (int) $parentResponse->json('folder.id');

        $childResponse = $this->postJson(route('admin.media_library.folders.store'), [
            'name' => 'Antalya',
            'parent_id' => $parentId,
        ]);
        $childResponse
            ->assertCreated()
            ->assertJsonPath('folder.name', 'Antalya')
            ->assertJsonPath('folder.parent_id', $parentId);

        $this->getJson(route('admin.media_library.folders.index'))
            ->assertOk()
            ->assertJsonPath('folders.0.name', 'Properties')
            ->assertJsonPath('folders.0.parent_id', null)
            ->assertJsonPath('folders.1.name', 'Antalya')
            ->assertJsonPath('folders.1.parent_id', $parentId);
    }

    public function test_admin_can_move_folder_under_another_parent_and_rejects_cycles(): void
    {
        $this->actingAs($this->admin());

        $parent = MediaFolder::query()->create([
            'name' => 'Parent',
            'slug' => 'parent',
            'user_id' => auth()->id(),
        ]);
        $child = MediaFolder::query()->create([
            'name' => 'Child',
            'slug' => 'child',
            'parent_id' => $parent->id,
            'user_id' => auth()->id(),
        ]);
        $other = MediaFolder::query()->create([
            'name' => 'Other',
            'slug' => 'other',
            'user_id' => auth()->id(),
        ]);

        $this->patchJson(route('admin.media_library.folders.update', $parent), [
            'name' => 'Parent',
            'parent_id' => $child->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['parent_id']);

        $this->patchJson(route('admin.media_library.folders.update', $child), [
            'name' => 'Child',
            'parent_id' => $other->id,
        ])
            ->assertOk()
            ->assertJsonPath('folder.parent_id', $other->id);
    }

    public function test_deleting_a_folder_archives_nested_media_and_removes_subfolders(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());

        $parent = MediaFolder::query()->create([
            'name' => 'Old Campaign',
            'slug' => 'old-campaign',
            'user_id' => auth()->id(),
        ]);
        $child = MediaFolder::query()->create([
            'name' => 'Nested',
            'slug' => 'nested',
            'parent_id' => $parent->id,
            'user_id' => auth()->id(),
        ]);
        $media = Media::query()->create($this->mediaAttributes([
            'folder_id' => $child->id,
            'path' => 'media-library/nested/photo.jpg',
        ]));
        Storage::disk('public')->put($media->path, 'image');

        $this->deleteJson(route('admin.media_library.folders.destroy', $parent))
            ->assertOk();

        $this->assertDatabaseMissing('media_folders', ['id' => $parent->id]);
        $this->assertDatabaseMissing('media_folders', ['id' => $child->id]);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'folder_id' => null,
        ]);
        $this->assertNotNull(Media::query()->find($media->id)?->archived_at);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_deleting_a_folder_archives_its_media_and_deletes_the_folder(): void
    {
        Storage::fake('public');
        $this->actingAs($this->admin());
        $folder = MediaFolder::query()->create([
            'name' => 'Old Campaign',
            'slug' => 'old-campaign',
            'user_id' => auth()->id(),
        ]);
        $media = Media::query()->create($this->mediaAttributes([
            'folder_id' => $folder->id,
            'path' => 'media-library/old-campaign/photo.jpg',
        ]));
        Storage::disk('public')->put($media->path, 'image');

        $this->deleteJson(route('admin.media_library.folders.destroy', $folder))
            ->assertOk();

        $this->assertDatabaseMissing('media_folders', ['id' => $folder->id]);
        $this->assertDatabaseHas('media', [
            'id' => $media->id,
            'folder_id' => null,
        ]);
        $this->assertNotNull(Media::query()->find($media->id)?->archived_at);
        Storage::disk('public')->assertExists($media->path);
    }

    private function admin(): User
    {
        $permission = Permission::findOrCreate('Media Library Management', 'web');
        $user = User::query()->create([
            'name' => 'Media Admin',
            'email' => Str::uuid().'@example.test',
            'mobile' => (string) random_int(1000000000, 9999999999),
            'password' => 'password',
            'type' => 'admin',
        ]);
        $user->givePermissionTo($permission);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function mediaAttributes(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Image',
            'path' => 'media-library/image.jpg',
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => 100,
            'user_id' => auth()->id(),
        ], $overrides);
    }
}
