<?php

namespace app\models;

use app\models\forms\post\PostForm;
use RuntimeException;
use Yii;
use yii\web\UploadedFile;

class PostHandler extends Post
{
    public array $tags = [];
    private bool $syncTagsAfterSave = false;

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

            if (!$this->save(false)) {
                throw new RuntimeException('Failed to create post.');
            }

            if ($form->imageFile instanceof UploadedFile) {
                $file = $this->createPostFile($this->id, $form->imageFile);
                $newFilePath = $file->path;
                $this->thumbnail_file_id = $file->id;

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

            if ($form->imageFile instanceof UploadedFile) {
                $newFile = $this->createPostFile($post->id, $form->imageFile);
                $newFilePath = $newFile->path;
                $post->thumbnail_file_id = $newFile->id;
            }

            if (!$post->save(false)) {
                throw new RuntimeException('Failed to update post.');
            }

            if ($oldFile && $post->thumbnail_file_id !== $oldFile->id) {
                PostFile::deleteAll([
                    'post_id' => $post->id,
                    'file_id' => $oldFile->id,
                    'type' => 'thumbnail',
                ]);

                if (!$oldFile->delete()) {
                    throw new RuntimeException('Failed to delete old thumbnail file record.');
                }
            }

            $transaction->commit();

            if ($oldFilePath && $post->thumbnail_file_id !== $oldFile?->id) {
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
            $url = Yii::$app->r2->upload($file, 'thumbnail');
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
            $postFile->type = 'thumbnail';
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

//    public function updatePostFile(int $id, PostFile $postFile)
//    {
//        $post = self::findOne($id);
//        if (!$post instanceof self) {
//            throw new RuntimeException('Post not found.');
//        }
//
//    }

    private function findPost(int $id): self
    {
        $post = self::findOne($id);

        if (!$post instanceof self) {
            throw new RuntimeException('Post not found.');
        }

        return $post;
    }
}
