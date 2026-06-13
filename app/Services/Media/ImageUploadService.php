<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;

class ImageUploadService
{
    protected int $quality = 75;
    protected int $maxWidth = 1200;

    public function upload(
        UploadedFile $file,
        string $folder,
        int $quality = 75,
        int $maxWidth = 1200
    ): string {

        $filename = Str::uuid() . '.jpg';
        $path     = $folder . '/' . $filename;

        $manager = new ImageManager(new Driver());

        // Decode the uploaded file
        $image = $manager->decode($file->getContent());

        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        // NEW v4 way to encode as JPEG
        $encoded = $image->encode(new JpegEncoder(quality: $quality));

        Storage::disk('public')->put($path, $encoded);

        return $path;
    }

    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function url(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
