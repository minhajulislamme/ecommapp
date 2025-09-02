<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    /**
     * Ensure directory exists, create if it doesn't
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }

    /**
     * Upload and optimize image to specified directory
     */
    public function uploadImage(UploadedFile $file, string $directory, ?string $oldImage = null): string
    {
        // Delete old image if exists
        if ($oldImage) {
            $this->deleteImage($directory, $oldImage);
        }

        // Ensure upload directory exists
        $uploadDir = public_path("upload/{$directory}");
        $this->ensureDirectoryExists($uploadDir);

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = public_path("upload/{$directory}/" . $filename);

        // Create and optimize image
        $image = $this->imageManager->read($file->getRealPath());

        // Resize to maximum 400x400 while maintaining aspect ratio
        $image->scale(width: 400, height: 400);

        // Save optimized image
        $image->save($path, quality: 85);

        return $filename;
    }

    /**
     * Delete image from specified directory
     */
    public function deleteImage(string $directory, string $filename): bool
    {
        $path = public_path("upload/{$directory}/" . $filename);

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * Upload and optimize user profile image
     */
    public function uploadUserImage(UploadedFile $file, ?string $oldImage = null): string
    {
        return $this->uploadImage($file, 'users', $oldImage);
    }

    /**
     * Delete user image
     */
    public function deleteUserImage(string $filename): bool
    {
        return $this->deleteImage('users', $filename);
    }

    /**
     * Get valid image mime types
     */
    public function getValidMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ];
    }

    /**
     * Get valid image extensions
     */
    public function getValidExtensions(): array
    {
        return ['jpeg', 'jpg', 'png', 'gif', 'webp'];
    }

    /**
     * Validate image file
     */
    public function validateImage(UploadedFile $file): array
    {
        $errors = [];

        // Check file size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            $errors[] = 'Image file size must be less than 5MB.';
        }

        // Check mime type
        if (!in_array($file->getMimeType(), $this->getValidMimeTypes())) {
            $errors[] = 'Image must be a valid format (JPEG, PNG, GIF, WebP).';
        }

        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->getValidExtensions())) {
            $errors[] = 'Image file extension is not allowed.';
        }

        return $errors;
    }
}
