<?php

namespace Modules\Base\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Base\Models\Media;
use Modules\Base\Models\MediaFolder;
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
            'type' => ['nullable', Rule::in(['all', 'image', 'jpeg', 'png', 'gif', 'webp'])],
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
        } elseif (in_array($type, ['jpeg', 'png', 'gif', 'webp'], true)) {
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
            ->get()
            ->map(fn (MediaFolder $folder) => [
                'id' => $folder->id,
                'name' => $folder->name,
                'media_count' => (int) $folder->media_count,
            ]);

        return response()->json(['folders' => $folders]);
    }

    public function storeFolder(Request $request): JsonResponse
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:media_folders,name'],
        ]);

        $folder = MediaFolder::query()->create([
            'name' => trim($payload['name']),
            'slug' => $this->uniqueFolderSlug($payload['name']),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => __('The Operation Done Successfully'),
            'folder' => [
                'id' => $folder->id,
                'name' => $folder->name,
                'media_count' => 0,
            ],
        ], 201);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp', 'max:4096'],
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
        $folder->load('media');

        foreach ($folder->media as $media) {
            $media->archive();
            $media->forceFill(['folder_id' => null])->save();
        }

        $folder->delete();

        return response()->json([
            'message' => __('The Operation Done Successfully'),
        ]);
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
