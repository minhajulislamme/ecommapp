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
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
    }

    /**
     * Upload and optimize image to specified directory
     */
    public function uploadImage(UploadedFile $file, string $directory, ?string $oldImage = null): string
    {
        // Validate the uploaded file first
        $validationErrors = $this->validateImage($file);
        if (!empty($validationErrors)) {
            throw new \InvalidArgumentException(implode(' ', $validationErrors));
        }

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

        try {
            // Create and optimize image
            $image = $this->imageManager->read($file->getRealPath());

            // Resize to maximum 400x400 while maintaining aspect ratio
            $image->scale(width: 400, height: 400);

            // Save optimized image
            $image->save($path, quality: 85);
        } catch (\Exception $e) {
            // If image processing fails, fall back to simple file copy
            if (!move_uploaded_file($file->getRealPath(), $path)) {
                throw new \RuntimeException("Failed to upload image: " . $e->getMessage());
            }
        }

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
     * Upload multiple images for product gallery
     */
    public function uploadGalleryImages(array $files, string $directory): array
    {
        $uploadedFiles = [];

        foreach ($files as $file) {
            try {
                $filename = $this->uploadImage($file, $directory);
                $uploadedFiles[] = $filename;
            } catch (\Exception $e) {
                // If one image fails, clean up any already uploaded images
                foreach ($uploadedFiles as $uploadedFile) {
                    $this->deleteImage($directory, $uploadedFile);
                }
                throw new \RuntimeException("Failed to upload gallery images: " . $e->getMessage());
            }
        }

        return $uploadedFiles;
    }

    /**
     * Delete multiple images from gallery
     */
    public function deleteGalleryImages(array $filenames, string $directory): int
    {
        $deletedCount = 0;

        foreach ($filenames as $filename) {
            if ($this->deleteImage($directory, $filename)) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Upload product images (main + gallery)
     */
    public function uploadProductImages(?\Illuminate\Http\UploadedFile $mainImage, array $galleryFiles = [], ?string $oldMainImage = null): array
    {
        $result = [
            'main_image' => null,
            'gallery_images' => []
        ];

        // Upload main image if provided
        if ($mainImage) {
            $result['main_image'] = $this->uploadImage($mainImage, 'products', $oldMainImage);
        }

        // Upload gallery images if provided
        if (!empty($galleryFiles)) {
            $result['gallery_images'] = $this->uploadGalleryImages($galleryFiles, 'products');
        }

        return $result;
    }

    /**
     * Delete product images (main + gallery)
     */
    public function deleteProductImages(?string $mainImage, array $galleryImages = []): bool
    {
        $success = true;

        // Delete main image
        if ($mainImage) {
            $success &= $this->deleteImage('products', $mainImage);
        }

        // Delete gallery images
        if (!empty($galleryImages)) {
            $this->deleteGalleryImages($galleryImages, 'products');
        }

        return $success;
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
