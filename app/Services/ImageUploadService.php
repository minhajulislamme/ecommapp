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
     * Upload and optimize user profile image
     */
    public function uploadUserImage(UploadedFile $file, ?string $oldImage = null): string
    {
        // Delete old image if exists
        if ($oldImage) {
            $this->deleteUserImage($oldImage);
        }

        // Generate unique filename
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = public_path('upload/users/' . $filename);

        // Create and optimize image
        $image = $this->imageManager->read($file->getRealPath());

        // Resize to maximum 400x400 while maintaining aspect ratio
        $image->scale(width: 400, height: 400);

        // Save optimized image
        $image->save($path, quality: 85);

        return $filename;
    }

    /**
     * Delete user image
     */
    public function deleteUserImage(string $filename): bool
    {
        $path = public_path('upload/users/' . $filename);

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
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
