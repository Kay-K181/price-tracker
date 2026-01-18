<?php

namespace App\Services;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;

class S3Service
{
    private S3Client $s3Client;
    private string $bucket;

    public function __construct()
    {
        //The bucket name (like a folder name)
        $this->bucket = 'price-tracker-images';

        //Creates an AWS S3 client that can talk to S3
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    public function uploadImage(string $imageUrl, string $filename): ?string
    {
        try {
            // Download image from source URL
            $imageContent = file_get_contents($imageUrl);

            if ($imageContent === false) {
                return null;
            }

            // Upload to S3
            $key = 'products/' . $filename;

            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $imageContent,
                'ContentType' => $this->getContentType($imageUrl),
            ]);

            // Return S3 URL
            return $this->getS3Url($key);

        } catch (\Exception $e) {
            \Log::error('S3 upload failed', [
                'url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function getContentType(string $url): string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

        return match(strtolower($extension)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };
    }

    private function getS3Url(string $key): string
    {
        return sprintf(
            '%s/%s/%s',
            env('AWS_ENDPOINT'),
            $this->bucket,
            $key
        );
    }
}
