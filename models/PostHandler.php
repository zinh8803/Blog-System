<?php

namespace app\models;

use app\models\forms\post\PostForm;
use RuntimeException;
use Yii;

class PostHandler extends Post
{
    public array $tags = [];
    private bool $syncTagsAfterSave = false;

    public function createFromForm(PostForm $form): self
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->setAttributes($form->getAttributes([
                'title',
                'summary',
                'content',
                'status',
                'category_id',
                'thumbnail_file_id',
            ]), false);
            $this->tags = $form->tags;
            $this->syncTagsAfterSave = true;

            if (!$this->save(false)) {
                throw new RuntimeException('Failed to create post.');
            }

            $transaction->commit();

            return $this;
        } catch (\Throwable $exception) {
            $transaction->rollBack();
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

        try {
            $post->setAttributes($form->getAttributes([
                'title',
                'summary',
                'content',
                'status',
                'category_id',
                'thumbnail_file_id',
            ]), false);
            $post->tags = $form->tags;
            $post->syncTagsAfterSave = $form->hasTagsInput;

            if (!$post->save(false)) {
                throw new RuntimeException('Failed to update post.');
            }

            $transaction->commit();

            return $post;
        } catch (\Throwable $exception) {
            $transaction->rollBack();
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

    private function findPost(int $id): self
    {
        $post = self::findOne($id);

        if (!$post instanceof self) {
            throw new RuntimeException('Post not found.');
        }

        return $post;
    }
}
