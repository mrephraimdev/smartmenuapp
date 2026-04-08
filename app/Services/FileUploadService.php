<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class FileUploadService
{
    protected string $disk = 'public';

    /**
     * Upload tenant logo
     */
    public function uploadLogo(UploadedFile $file, int $tenantId): string
    {
        return $this->uploadImage($file, "tenants/{$tenantId}/logo", 200, 200);
    }

    /**
     * Upload tenant cover image
     */
    public function uploadCover(UploadedFile $file, int $tenantId): string
    {
        return $this->uploadImage($file, "tenants/{$tenantId}/cover", 1200, 400);
    }

    /**
     * Upload dish photo
     */
    public function uploadDishPhoto(UploadedFile $file, int $dishId): string
    {
        return $this->uploadImage($file, "dishes/{$dishId}", 800, 600);
    }

    /**
     * Upload category image
     */
    public function uploadCategoryImage(UploadedFile $file, int $categoryId): string
    {
        return $this->uploadImage($file, "categories/{$categoryId}", 400, 300);
    }

    /**
     * Upload generic image with resize
     */
    public function uploadImage(
        UploadedFile $file,
        string $path,
        int $maxWidth = 800,
        int $maxHeight = 600,
        int $quality = 85
    ): string {
        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath = $path . '/' . $filename;

        // Read the image
        $image = Image::read($file);

        // Resize if needed (maintain aspect ratio)
        $image->scaleDown($maxWidth, $maxHeight);

        // Encode and save
        $encoded = $image->toJpeg($quality);
        Storage::disk($this->disk)->put($fullPath, $encoded);

        return Storage::disk($this->disk)->url($fullPath);
    }

    /**
     * Upload file without processing (PDF, etc.)
     */
    public function uploadFile(UploadedFile $file, string $path): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $fullPath = $file->storeAs($path, $filename, $this->disk);

        return Storage::disk($this->disk)->url($fullPath);
    }

    /**
     * Delete file from storage
     */
    public function delete(string $url): bool
    {
        // Extract path from URL
        $path = str_replace(Storage::disk($this->disk)->url(''), '', $url);

        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Delete all files in a directory
     */
    public function deleteDirectory(string $path): bool
    {
        if (Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->deleteDirectory($path);
        }

        return false;
    }

    /**
     * Get allowed image mime types
     */
    public static function allowedImageMimes(): array
    {
        return ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    }

    /**
     * Get allowed image extensions
     */
    public static function allowedImageExtensions(): string
    {
        return 'jpeg,jpg,png,gif,webp';
    }

    /**
     * Validate image file with security checks
     *
     * @param int $maxSizeKb Maximum file size in KB
     * @param int $maxWidth Maximum image width (0 = no limit)
     * @param int $maxHeight Maximum image height (0 = no limit)
     */
    public static function imageValidationRules(
        int $maxSizeKb = 2048,
        int $maxWidth = 4000,
        int $maxHeight = 4000
    ): array {
        $rules = [
            'file',
            'image', // Vérifie que c'est une vraie image (pas juste l'extension)
            'mimes:' . self::allowedImageExtensions(),
            'max:' . $maxSizeKb,
        ];

        // Ajouter validation des dimensions si spécifiées
        if ($maxWidth > 0 || $maxHeight > 0) {
            $dimensions = 'dimensions:';
            $parts = [];
            if ($maxWidth > 0) {
                $parts[] = "max_width={$maxWidth}";
            }
            if ($maxHeight > 0) {
                $parts[] = "max_height={$maxHeight}";
            }
            $rules[] = $dimensions . implode(',', $parts);
        }

        return $rules;
    }

    /**
     * Verify the actual MIME type of an uploaded file
     * (defense in depth - validates content, not just extension)
     */
    public static function verifyImageContent(UploadedFile $file): bool
    {
        // Vérifier le MIME type réel du fichier
        $mimeType = $file->getMimeType();

        if (!in_array($mimeType, self::allowedImageMimes())) {
            return false;
        }

        // Tenter de lire comme image pour valider le contenu
        try {
            $imageInfo = @getimagesize($file->getPathname());
            return $imageInfo !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
