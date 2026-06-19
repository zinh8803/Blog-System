<?php

namespace app\models;

use app\models\forms\post\PostForm;
use RuntimeException;
use Yii;
use yii\web\UploadedFile;

class PostHandler extends Post
{
    private const FILE_TYPE_THUMBNAIL = 'thumbnail';
    private const FILE_TYPE_CONTENT = 'content';

    public array $tags = [];
    private bool $syncTagsAfterSave = false;
    private bool $syncContentImagesAfterSave = false;

    public function createFromForm(PostForm $form): self
    {
        $transaction = Yii::$app->db->beginTransaction();
        $newFilePath = null;

        try {
            $this->setAttributes($form->getAttributes([
                'title',
                'summary',
                'content',
                'status',
                'category_id',
            ]), false);
            $this->tags = $form->tags;
            $this->syncTagsAfterSave = true;
            $this->syncContentImagesAfterSave = !($form->imageFile instanceof UploadedFile);

            if (!$this->save(false)) {
                throw new RuntimeException('Failed to create post.');
            }

            if ($form->imageFile instanceof UploadedFile) {
                $file = $this->createPostFile($this->id, $form->imageFile);
                $newFilePath = $file->path;
                $this->thumbnail_file_id = $file->id;
                $this->syncContentImagesAfterSave = true;

                if (!$this->save(false, ['thumbnail_file_id'])) {
                    throw new RuntimeException('Failed to update thumbnail file.');
                }
            }

            $transaction->commit();

            return $this;
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            if ($newFilePath) {
                Yii::$app->r2->delete($newFilePath);
            }

            throw $exception;
        }
    }

    public function updateFromForm(int $id, PostForm $form): self
    {
        $post = self::findOne($id);

        if (!$post instanceof self) {
            throw new RuntimeException('Post not found.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        $oldFile = $post->getThumbnailFile()->one();
        $oldFilePath = $oldFile?->path;
        $newFilePath = null;

        try {
            $post->setAttributes($form->getAttributes([
                'title',
                'summary',
                'content',
                'status',
                'category_id',
            ]), false);
            $post->tags = $form->tags;
            $post->syncTagsAfterSave = $form->hasTagsInput;
            $post->syncContentImagesAfterSave = true;

            if ($form->imageFile instanceof UploadedFile) {
                $newFile = $this->createPostFile($post->id, $form->imageFile);
                $newFilePath = $newFile->path;
                $post->thumbnail_file_id = $newFile->id;
            }

            if (!$post->save(false)) {
                throw new RuntimeException('Failed to update post.');
            }

            $thumbnailChanged = $oldFile && (int) $post->thumbnail_file_id !== (int) $oldFile->id;

            if ($thumbnailChanged) {
                PostFile::deleteAll([
                    'post_id' => $post->id,
                    'file_id' => $oldFile->id,
                    'type' => self::FILE_TYPE_THUMBNAIL,
                ]);

                if (!$oldFile->delete()) {
                    throw new RuntimeException('Failed to delete old thumbnail file record.');
                }
            }

            $transaction->commit();

            if ($oldFilePath && $thumbnailChanged) {
                Yii::$app->r2->delete($oldFilePath);
            }

            return $post;
        } catch (\Throwable $exception) {
            $transaction->rollBack();

            if ($newFilePath) {
                Yii::$app->r2->delete($newFilePath);
            }

            throw $exception;
        }
    }

    public function forceDeleteById(int $id): void
    {
        $post = $this->findPost($id);
        $transaction = Yii::$app->db->beginTransaction();

        try {
            if ($post->thumbnailFile?->path) {
                Yii::$app->r2->delete($post->thumbnailFile?->path);
            }
            PostTag::deleteAll(['post_id' => $post->id]);
            PostFile::deleteAll(['post_id' => $post->id]);
            Comment::deleteAll(['post_id' => $post->id]);
            Like::deleteAll(['post_id' => $post->id]);

            if (!$post->delete()) {
                throw new RuntimeException('Failed to force delete post.');
            }

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            throw $exception;
        }
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if ($this->syncTagsAfterSave) {
            $this->syncTags();
            $this->syncTagsAfterSave = false;
        }

        if ($this->syncContentImagesAfterSave) {
            $this->syncContentImageFiles($this->content);
            $this->syncContentImagesAfterSave = false;
        }
    }

    private function syncTags(): void
    {
        $tagIds = [];

        foreach (Tag::findOrCreateByNames($this->tags) as $tag) {
            $tagIds[$tag->id] = $tag->id;
        }

        PostTag::deleteAll(['post_id' => $this->id]);

        if ($tagIds === []) {
            return;
        }

        $rows = array_map(
            fn($tagId) => [$this->id, $tagId],
            array_values($tagIds)
        );

        Yii::$app->db->createCommand()
            ->batchInsert(PostTag::tableName(), ['post_id', 'tag_id'], $rows)
            ->execute();
    }

    private function createPostFile(int $postId, UploadedFile $file): File
    {
        $uploadedFilePath = null;

        try {
            $url = Yii::$app->r2->upload($file, self::FILE_TYPE_THUMBNAIL);
            $uploadedFilePath = $url['key'];

            $model = new File();
            $model->created_by = Yii::$app->user->id;
            $model->original_name = $file->name;
            $model->path = $url['key'];
            $model->url = $url['url'];
            $model->mime_type = $file->type;
            $model->size = $file->size;
            if (!$model->save(false)) {
                throw new RuntimeException('Failed to save file.');
            }

            $postFile = new PostFile();
            $postFile->post_id = $postId;
            $postFile->file_id = $model->id;
            $postFile->type = self::FILE_TYPE_THUMBNAIL;
            if (!$postFile->save(false)) {
                throw new RuntimeException('Failed to associate file with post.');
            }

            return $model;
        } catch (\Exception $e) {
            if ($uploadedFilePath) {
                Yii::$app->r2->delete($uploadedFilePath);
            }

            throw new RuntimeException($e->getMessage());
        }
    }

    private function extractImageSrcFromContent(string $content): array
    {
        if (trim($content) === '') {
            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8"><body>' . $content . '</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches);

            return array_values(array_unique(array_filter($matches[1] ?? [])));
        }

        $sources = [];
        foreach ($document->getElementsByTagName('img') as $image) {
            $source = trim($image->getAttribute('src'));

            if ($source !== '') {
                $sources[] = $source;
            }
        }

        return array_values(array_unique($sources));
    }

    private function normalizeImagePath(string $src): ?string
    {
        $publicUrl = rtrim(Yii::$app->r2->public_url, '/');
        $source = trim($src);

        if ($source === '') {
            return null;
        }

        if ($publicUrl !== '' && str_starts_with($source, $publicUrl)) {
            $source = substr($source, strlen($publicUrl));
        } elseif (preg_match('#^https?://#i', $source) || str_starts_with($source, '//')) {
            return null;
        }

        $path = parse_url($source, PHP_URL_PATH);

        if (!is_string($path) || $path === '') {
            return null;
        }

        return ltrim(rawurldecode($path), '/');
    }

    private function syncContentImageFiles(string $content): void
    {
        $paths = [];

        foreach ($this->extractImageSrcFromContent($content) as $imageSrc) {
            $path = $this->normalizeImagePath($imageSrc);

            if ($path !== null) {
                $paths[$path] = $path;
            }
        }

        PostFile::deleteAll([
            'post_id' => $this->id,
            'type' => self::FILE_TYPE_CONTENT,
        ]);

        if ($paths === []) {
            return;
        }

        $files = File::find()
            ->select(['id', 'path'])
            ->where(['path' => array_values($paths)])
            ->all();

        if ($files === []) {
            return;
        }

        $fileIdsByPath = [];
        foreach ($files as $file) {
            $fileIdsByPath[$file->path] = (int) $file->id;
        }

        $now = time();
        $rows = [];
        $sortOrder = 0;
        $addedFileIds = [];

        foreach ($paths as $path) {
            $fileId = $fileIdsByPath[$path] ?? null;

            if (
                $fileId === null
                || $fileId === (int) $this->thumbnail_file_id
                || isset($addedFileIds[$fileId])
            ) {
                continue;
            }

            $addedFileIds[$fileId] = true;
            $rows[] = [
                $this->id,
                $fileId,
                self::FILE_TYPE_CONTENT,
                $sortOrder++,
                $now,
                $now,
            ];
        }

        if ($rows === []) {
            return;
        }

        Yii::$app->db->createCommand()
            ->batchInsert(
                PostFile::tableName(),
                ['post_id', 'file_id', 'type', 'sort_order', 'created_at', 'updated_at'],
                $rows
            )
            ->execute();
    }

    private function findPost(int $id): self
    {
        $post = self::findOne($id);

        if (!$post instanceof self) {
            throw new RuntimeException('Post not found.');
        }

        return $post;
    }
}
