<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class S3Service
{
    /**
     * Upload a file to S3
     */
    public function uploadFile($file, $path = 'Connect/Distributor/')
    {
        $uploadPath = trim($path, '/');
        Storage::disk('s3')->put($uploadPath, file_get_contents($file), 'public');

        return [
            'success' => true,
            'url' => Storage::disk('s3')->url($uploadPath),
            'key' => $uploadPath
        ];
    }

    /**
     * Get File URL from S3
     */
    public function getFileUrl($filePath)
    {
        return Storage::disk('s3')->url($filePath);
    }
}
