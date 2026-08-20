<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Base\Models\Media;
use Modules\Base\Models\MediaFolder;
use Modules\Base\Support\Media\MediaAssetResolver;
use Modules\Core\Traits\FileTrait;

class MediaLibraryController extends Controller
{
    use FileTrait;

    public function __construct()
    {
        $this->setActive('cms');
        $this->setActive('media_library');
    }

    public function index()
    {
        return view('base::admin.media.index');
    }

    public function list(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'folder_id' => ['nullable'],
            'type' => ['nullable', Rule::in(['all', 'image', 'jpeg', 'png', 'gif', 'webp', 'avif'])],
            'date' => ['nullable', 'date_format:Y-m'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'name_asc', 'name_desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:12', 'max:96'],
        ]);

        $query = Media::query()->active()->with(['user:id,name', 'folder:id,name']);
        $search = trim((string) ($payload['q'] ?? ''));
        $folderId = $payload['folder_id'] ?? null;
        $type = $payload['type'] ?? 'all';
        $date = $payload['date'] ?? null;
        $sort = $payload['sort'] ?? 'newest';
        $perPage = (int) ($payload['per_page'] ?? 24);

        if ($folderId === 'root') {
            $query->whereNull('folder_id');
        } elseif ($folderId !== null && $folderId !== '') {
            $query->where('folder_id', (int) $folderId);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('path', 'like', '%'.$search.'%')
                    ->orWhere('alt_text', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%')
                    ->orWhere('caption', 'like', '%'.$search.'%');
            });
        }

        if ($type === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif (in_array($type, ['jpeg', 'png', 'gif', 'webp', 'avif'], true)) {
            $mime = $type === 'jpeg' ? 'image/jpeg' : 'image/'.$type;
            $query->where('mime_type', $mime);
        }

        if (is_string($date) && $date !== '') {
            [$year, $month] = array_map('intval', explode('-', $date));
            $query->whereYear('created_at', $year)->whereMonth('created_at', $month);
        }

        match ($sort) {
            'oldest' => $query->orderBy('id'),
            'name_asc' => $query->orderBy('name')->orderByDesc('id'),
            'name_desc' => $query->orderByDesc('name')->orderByDesc('id'),
            default => $query->latest('id'),
        };

        $items = $query->paginate($perPage)->through(
            fn (Media $media) => $this->serializeMedia($media)
        );

        return response()->json($items);
    }

    public function folders(): JsonResponse
    {
        $folders = MediaFolder::query()
            ->withCount(['media as media_count' => fn ($query) => $query->active()])
            ->orderBy('name')
            ->get();

        $serialized = collect(MediaFolder::sortTree($folders))
            ->map(fn (MediaFolder $folder) => $this->serializeFolder($folder))
            ->values();

        return response()->json(['folders' => $serialized]);
    }

    /**
     * Resolve an active library image by storage path (used by the admin picker to
     * open on the folder that already holds the field's current image).
     */
    public function resolve(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'path' => ['required', 'string', 'max:2048'],
        ]);

        $path = MediaAssetResolver::normalizePath($payload['path']);
        abort_if($path === null, 404);

        $media = Media::query()
            ->active()
            ->where('disk', 'public')
            ->where('path', $path)
            ->with(['user:id,name', 'folder:id,name'])
            ->first();

        abort_if($media === null, 404);

        return response()->json([
            'item' => $this->serializeMedia($media),
        ]);
    }

    public function storeFolder(Request $request): JsonResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'parent_id' => $this->nullableParentId($request->input('parent_id')),
        ]);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $parentId = $payload['parent_id'] ?? null;
        $this->assertUniqueFolderName($payload['name'], $parentId);

        $folder = MediaFolder::query()->create([
            'name' => $payload['name'],
            'slug' => $this->uniqueFolderSlug($payload['name']),
            'parent_id' => $parentId,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('The Operation Done Successfully'),
            'folder' => $this->serializeFolder($folder->fresh()),
        ], 201);
    }

    public function updateFolder(Request $request, MediaFolder $folder): JsonResponse
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'parent_id' => $request->exists('parent_id')
                ? $this->nullableParentId($request->input('parent_id'))
                : $folder->parent_id,
        ]);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        $parentId = array_key_exists('parent_id', $payload) ? ($payload['parent_id'] ?? null) : $folder->parent_id;
        $this->assertParentNotCyclic($folder, $parentId);
        $this->assertUniqueFolderName($payload['name'], $parentId, $folder->id);

        // Keep slug unchanged so on-disk paths under media-library/{id}-{slug} stay valid.
        $folder->update([
            'name' => $payload['name'],
            'parent_id' => $parentId,
        ]);

        $folder->loadCount(['media as media_count' => fn ($query) => $query->active()]);

        return response()->json([
            'message' => __('The Operation Done Successfully'),
            'folder' => $this->serializeFolder($folder),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,avif', 'max:4096'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp,avif', 'max:4096'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ]);

        $files = [];
        if ($request->hasFile('file')) {
            $files[] = $request->file('file');
        }
        if ($request->hasFile('files')) {
            $files = array_merge($files, (array) $request->file('files'));
        }

        abort_if(count($files) === 0, 422, __('No files uploaded'));

        $folder = $request->filled('folder_id')
            ? MediaFolder::query()->findOrFail((int) $request->input('folder_id'))
            : null;
        $directory = $folder?->storage_path ?? 'media-library';
        $createdItems = [];
        foreach ($files as $file) {
            /** @var UploadedFile $file */
            $path = $this->upload($file, $directory);
            [$width, $height] = $this->imageDimensions($file);
            $media = Media::query()->firstOrCreate(
                ['path' => $path],
                [
                    'folder_id' => $folder?->id,
                    'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'alt_text' => $request->input('alt_text'),
                    'title' => $request->input('title'),
                    'caption' => $request->input('caption'),
                    'disk' => 'public',
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'width' => $width,
                    'height' => $height,
                    'user_id' => auth()->id(),
                ]
            );

            $createdItems[] = $this->serializeMedia($media->loadMissing(['user:id,name', 'folder:id,name']));
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('Uploaded successfully'),
                'items' => $createdItems,
            ]);
        }

        session()->flushMessage(true, __('The Operation Done Successfully'));

        return back();
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $payload = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);

        if (array_key_exists('name', $payload) && is_string($payload['name'])) {
            $payload['name'] = trim($payload['name']) ?: $media->name;
        }

        if ($request->exists('folder_id') && ($request->input('folder_id') === null || $request->input('folder_id') === '')) {
            $payload['folder_id'] = null;
        }

        $media->update($payload);

        return response()->json([
            'message' => __('The Operation Done Successfully'),
            'item' => $this->serializeMedia($media->fresh(['user:id,name', 'folder:id,name'])),
        ]);
    }

    public function deleteMulti(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'exists:media,id'],
        ]);

        $items = Media::query()->whereIn('id', $payload['ids'])->get();
        foreach ($items as $item) {
            $item->archive();
        }

        return response()->json([
            'message' => __('The Operation Done Successfully'),
        ]);
    }

    public function destroy(Request $request, Media $media): JsonResponse|RedirectResponse
    {
        $media->archive();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('The Operation Done Successfully'),
            ]);
        }

        session()->flushMessage(true, __('The Operation Done Successfully'));

        return back();
    }

    public function destroyFolder(MediaFolder $folder): JsonResponse
    {
        $folderIds = array_merge([$folder->id], MediaFolder::descendantIdsOf($folder->id));

        $items = Media::query()->whereIn('folder_id', $folderIds)->get();
        foreach ($items as $media) {
            $media->archive();
            $media->forceFill(['folder_id' => null])->save();
        }

        MediaFolder::query()
            ->whereIn('id', $folderIds)
            ->orderByDesc('id')
            ->get()
            ->each(fn (MediaFolder $item) => $item->delete());

        return response()->json([
            'message' => __('The Operation Done Successfully'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeFolder(MediaFolder $folder): array
    {
        if (! array_key_exists('media_count', $folder->getAttributes()) && ! isset($folder->media_count)) {
            $folder->loadCount(['media as media_count' => fn ($query) => $query->active()]);
        }

        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'parent_id' => $folder->parent_id,
            'media_count' => (int) ($folder->media_count ?? 0),
        ];
    }

    private function nullableParentId(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 'root') {
            return null;
        }

        return (int) $value;
    }

    private function assertUniqueFolderName(string $name, ?int $parentId, ?int $ignoreId = null): void
    {
        $query = MediaFolder::query()
            ->where('name', $name)
            ->when(
                $parentId === null,
                fn ($builder) => $builder->whereNull('parent_id'),
                fn ($builder) => $builder->where('parent_id', $parentId)
            );

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => __('A folder with this name already exists in the selected parent.'),
            ]);
        }
    }

    private function assertParentNotCyclic(MediaFolder $folder, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($parentId === $folder->id) {
            throw ValidationException::withMessages([
                'parent_id' => __('A folder cannot be its own parent.'),
            ]);
        }

        $blocked = array_merge([$folder->id], MediaFolder::descendantIdsOf($folder->id));
        if (in_array($parentId, $blocked, true)) {
            throw ValidationException::withMessages([
                'parent_id' => __('A folder cannot be moved into itself or one of its subfolders.'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeMedia(Media $media): array
    {
        return [
            'id' => $media->id,
            'folder_id' => $media->folder_id,
            'folder_name' => $media->folder?->name,
            'name' => $media->name,
            'alt_text' => $media->alt_text,
            'title' => $media->title,
            'caption' => $media->caption,
            'url' => $media->url,
            'path' => $media->path,
            'mime_type' => $media->mime_type,
            'size' => $media->size,
            'width' => $media->width,
            'height' => $media->height,
            'uploader' => $media->user?->name,
            'created_at' => optional($media->created_at)->toDateTimeString(),
            'created_at_human' => optional($media->created_at)?->diffForHumans(),
            'archived' => $media->archived_at !== null,
        ];
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function imageDimensions(UploadedFile $file): array
    {
        $size = @getimagesize($file->getRealPath() ?: $file->getPathname());
        if (! is_array($size)) {
            return [null, null];
        }

        return [(int) ($size[0] ?? 0) ?: null, (int) ($size[1] ?? 0) ?: null];
    }

    private function uniqueFolderSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'folder';
        $slug = $base;
        $suffix = 2;

        while (MediaFolder::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
