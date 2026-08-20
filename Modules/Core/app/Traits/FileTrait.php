<?php

namespace Modules\Core\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

trait FileTrait
{
    /**
     * Uploads a file or image to the specified directory.
     */
    public function upload(
        UploadedFile $file,
        string $dir,
        ?string $name = null,
        ?string $old = null,
        ?int $width = null,
        string $disk = 'public'
    ): ?string {
        // Validate the file
        if (! $this->isValidFile($file)) {
            return null;
        }

        // Remove an old file if specified
        if ($old) {
            $this->deleteFile($old, $disk);
        }

        // Generate a secure filename
        $filename = $this->generateFilename($file, $name);

        $mimeType = $file->getMimeType();

        // Store PDFs (and formats GD cannot process) without re-encoding.
        if ($mimeType === 'application/pdf' || $this->mustStoreWithoutProcessing($mimeType)) {
            return Storage::disk($disk)->putFileAs($dir, $file, $filename);
        }

        // Process and upload image via Intervention directly.
        // Avoid Intervention\Image\Laravel\Facades\Image — Laravel 13 binds
        // the same 'image' container key to Illuminate\Image\ImageManager,
        // whose GD driver calls ImageManager::usingDriver() (Intervention v4 API).
        $source = $file->getRealPath() ?: $file->getPathname();

        try {
            $image = ImageManager::gd()->read($source);
            if ($width) {
                $image->scale(width: $width);
            }

            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $encoded = $image->encodeByExtension($extension);
            Storage::disk($disk)->put($dir.'/'.$filename, (string) $encoded);

            return $dir.'/'.$filename;
        } catch (\Throwable) {
            // Fallback when the host GD build cannot decode/encode (e.g. AVIF).
            return Storage::disk($disk)->putFileAs($dir, $file, $filename);
        }
    }

    /**
     * Formats that should skip Intervention when the local GD build lacks support.
     */
    private function mustStoreWithoutProcessing(?string $mimeType): bool
    {
        if ($mimeType !== 'image/avif') {
            return false;
        }

        $info = function_exists('gd_info') ? gd_info() : [];

        return empty($info['AVIF Support']);
    }

    /**
     * Validates if a file is acceptable for upload.
     */
    private function isValidFile(UploadedFile $file): bool
    {
        if (! $file->isValid()) {
            session()->flushMessage(false, __('The selected file is not valid.'));

            return false;
        }

        $allowedMimeTypes = config('core.allowed_mime_types');
        $mimeType = $file->getMimeType();
        $isAllowedImage = is_string($mimeType) && str_starts_with($mimeType, 'image/');

        if (! $isAllowedImage && ! in_array($mimeType, $allowedMimeTypes, true)) {
            session()->flushMessage(false, __('File type is not allowed.'));

            return false;
        }

        return true;
    }

    /**
     * Deletes a file from the specified disk.
     */
    public function deleteFile(string $filename, string $disk = 'public'): void
    {
        try {
            Storage::disk($disk)->delete($filename);
        } catch (FileException $exception) {
            session()->flushMessage(false, __('An Error Occurred!'), $exception);
        }
    }

    /**
     * Generates a sanitized, secure filename.
     */
    private function generateFilename(UploadedFile $file, ?string $name = null): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedOriginalName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $originalName);
        $hash = md5($sanitizedOriginalName.time());

        return ($name ?? $hash).'.'.$file->getClientOriginalExtension();
    }

    /**
     * Deletes an entire directory from the specified disk.
     */
    public function deleteDir(string $dir, string $disk = 'public'): void
    {
        try {
            Storage::disk($disk)->deleteDirectory($dir);
        } catch (FileException $exception) {
            session()->flushMessage(false, __('An Error Occurred!'), $exception);
        }
    }
}
