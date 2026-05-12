<?php

declare(strict_types=1);

namespace Modules\Core\Support;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Admin Blade image-input (Metronic): map file field + optional media library path + remove flag.
 * When the user clicks Remove, the DB field should become null without deleting the file from storage.
 */
final class AdminImageInput
{
    /**
     * Sentinel passed through payloads to ContentPayloadBuilder / repositories meaning "clear image column".
     */
    public const REMOVED = '__ADMIN_IMAGE_REMOVED__';

    public static function removeFieldName(string $fileFieldName): string
    {
        if (preg_match('/^([^\[]+)\[([^\]]+)\]$/', $fileFieldName, $m)) {
            return $m[1].'_remove['.$m[2].']';
        }

        return $fileFieldName.'_remove';
    }

    public static function isRemoved(Request $request, string $fileFieldName): bool
    {
        $key = self::removeFieldName($fileFieldName);

        if (preg_match('/^([^\[]+)\[([^\]]+)\]$/', $key, $m)) {
            $dotKey = $m[1].'.'.$m[2];

            return $request->boolean($dotKey) || $request->input($dotKey) === '1';
        }

        return $request->boolean($key) || $request->input($key) === '1';
    }

    /**
     * @return UploadedFile|string|self::REMOVED|null
     */
    public static function resolveFileOrMediaPath(Request $request, string $fileFieldName, string $mediaPathInputName): mixed
    {
        if (self::isRemoved($request, $fileFieldName)) {
            return self::REMOVED;
        }

        $file = $request->file($fileFieldName);
        if ($file instanceof UploadedFile) {
            return $file;
        }

        $path = $request->input($mediaPathInputName);
        if (is_string($path)) {
            $path = trim($path);
            if ($path === '' || strcasecmp($path, 'null') === 0) {
                return null;
            }

            return $path;
        }

        return null;
    }
}
