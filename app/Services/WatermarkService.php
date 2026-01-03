<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class WatermarkService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Add watermark to an image and return the path to the watermarked version.
     */
    public function addWatermark(string $imagePath, ?string $watermarkText = null): string
    {
        $watermarkText = $watermarkText ?? 'Made with StyleDream';

        // Determine if it's a URL or a storage path
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $imageContent = file_get_contents($imagePath);
            $image = $this->manager->read($imageContent);
        } else {
            // It's a storage path
            $fullPath = Storage::disk('public')->path($imagePath);
            $image = $this->manager->read($fullPath);
        }

        $width = $image->width();
        $height = $image->height();

        // Calculate font size based on image dimensions (roughly 2.5% of width)
        $fontSize = max(16, (int)($width * 0.025));

        // Calculate padding
        $padding = max(10, (int)($width * 0.02));

        // Create watermark with semi-transparent background
        $textWidth = strlen($watermarkText) * ($fontSize * 0.6);
        $textHeight = $fontSize * 1.5;

        // Position at bottom right with padding
        $x = $width - $textWidth - $padding;
        $y = $height - $padding - ($fontSize / 2);

        // Add semi-transparent dark background for readability
        $image->drawRectangle(
            (int)($x - 10),
            (int)($y - $fontSize - 5),
            function ($draw) use ($textWidth, $textHeight) {
                $draw->size((int)($textWidth + 20), (int)($textHeight));
                $draw->background('rgba(0, 0, 0, 0.5)');
            }
        );

        // Add the watermark text
        $image->text($watermarkText, (int)$x, (int)$y, function ($font) use ($fontSize) {
            $font->size($fontSize);
            $font->color('rgba(255, 255, 255, 0.9)');
            $font->align('left');
            $font->valign('bottom');
        });

        // Generate unique filename for watermarked image
        $filename = 'watermarked/' . uniqid('wm_') . '.jpg';

        // Ensure directory exists
        Storage::disk('public')->makeDirectory('watermarked');

        // Encode and save
        $encoded = $image->toJpeg(90);
        Storage::disk('public')->put($filename, $encoded);

        return Storage::url($filename);
    }

    /**
     * Add watermark and return base64 encoded image for direct download/share.
     */
    public function addWatermarkBase64(string $imagePath, ?string $watermarkText = null): string
    {
        $watermarkText = $watermarkText ?? 'Made with StyleDream';

        // Determine if it's a URL or a storage path
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            $imageContent = file_get_contents($imagePath);
            $image = $this->manager->read($imageContent);
        } else {
            $fullPath = Storage::disk('public')->path($imagePath);
            $image = $this->manager->read($fullPath);
        }

        $width = $image->width();
        $height = $image->height();

        $fontSize = max(16, (int)($width * 0.025));
        $padding = max(10, (int)($width * 0.02));

        $textWidth = strlen($watermarkText) * ($fontSize * 0.6);
        $x = $width - $textWidth - $padding;
        $y = $height - $padding - ($fontSize / 2);

        // Add semi-transparent background
        $image->drawRectangle(
            (int)($x - 10),
            (int)($y - $fontSize - 5),
            function ($draw) use ($textWidth, $fontSize) {
                $draw->size((int)($textWidth + 20), (int)($fontSize * 1.5));
                $draw->background('rgba(0, 0, 0, 0.5)');
            }
        );

        // Add watermark text
        $image->text($watermarkText, (int)$x, (int)$y, function ($font) use ($fontSize) {
            $font->size($fontSize);
            $font->color('rgba(255, 255, 255, 0.9)');
            $font->align('left');
            $font->valign('bottom');
        });

        return $image->toJpeg(90)->toDataUri();
    }

    /**
     * Clean up old watermarked images (call via scheduled task).
     */
    public function cleanupOldWatermarks(int $hoursOld = 24): int
    {
        $deleted = 0;
        $files = Storage::disk('public')->files('watermarked');
        $cutoff = now()->subHours($hoursOld)->timestamp;

        foreach ($files as $file) {
            $lastModified = Storage::disk('public')->lastModified($file);
            if ($lastModified < $cutoff) {
                Storage::disk('public')->delete($file);
                $deleted++;
            }
        }

        return $deleted;
    }
}
