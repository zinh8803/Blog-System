<?php

namespace app\components;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use yii\base\Component;
use yii\web\UploadedFile;

class R2Component extends Component
{
    public $account;
    public $key;
    public $secret;
    public $bucket;
    public $public_url;

    private S3Client $client;

    public function init()
    {
        parent::init();

        $this->client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => "https://{$this->account}.r2.cloudflarestorage.com",
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key' => $this->key,
                'secret' => $this->secret,
            ],
        ]);
    }

    public function upload(UploadedFile $file, string $type = 'content'): array
    {
        $folder = $this->getFolder($type);
        $ext = $file->extension;
        $fileName = uniqid('', true) . '.' . $ext;
        $key = "{$folder}/{$fileName}";

        $this->client->putObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
            'SourceFile' => $file->tempName,
            'ContentType' => $file->type,
        ]);

        return $this->formatResponse($key, $file);
    }

    public function update(string $oldKey, UploadedFile $newFile, string $type = 'content'): array
    {
        try {
            $uploadedFile = $this->upload($newFile, $type);

            if (!empty($oldKey) && $this->exists($oldKey)) {
                $this->delete($oldKey);
            }

            return $uploadedFile;
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to update file: ' . $e->getMessage());
        }
    }

    public function delete(string $key): bool
    {
        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    public function exists(string $key): bool
    {
        try {
            return $this->client->doesObjectExist($this->bucket, $key);
        } catch (AwsException $e) {
            return false;
        }
    }

    public function getUrl(string $key): string
    {
        return rtrim($this->public_url, '/') . '/' . ltrim($key, '/');
    }

    public function getSignedUrl(string $key, string $expires = '+10 minutes'): string
    {
        $command = $this->client->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);

        $request = $this->client->createPresignedRequest($command, $expires);

        return (string) $request->getUri();
    }

    public function list(string $prefix = ''): array
    {
        $result = $this->client->listObjectsV2([
            'Bucket' => $this->bucket,
            'Prefix' => $prefix,
        ]);

        return $result['Contents'] ?? [];
    }

    public function copy(string $sourceKey, string $targetKey): bool
    {
        try {
            $this->client->copyObject([
                'Bucket' => $this->bucket,
                'CopySource' => "{$this->bucket}/{$sourceKey}",
                'Key' => $targetKey,
            ]);

            return true;
        } catch (AwsException $e) {
            return false;
        }
    }

    private function getFolder(string $type): string
    {
        $folders = [
            'thumbnail' => 'thumbnails',
            'content' => 'contents',
            'avatar' => 'avatars',
            'document' => 'documents',
        ];

        return $folders[$type] ?? 'uploads';
    }

    private function formatResponse(string $key, UploadedFile $file): array
    {
        return [
            'key' => $key,
            'url' => $this->getUrl($key),
            'name' => $file->name,
            'type' => $file->type,
            'size' => $file->size,
        ];
    }
}
