<?php

namespace app\models\forms\like;

use app\models\Like;
use RuntimeException;

class LikeForm extends Like
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [

        ]);
    }

    public function likePost(int $postId): void
    {
        $like = Like::findByPostId($postId);

        if ($like) {
            return;
        }

        $like = new Like();
        $like->post_id = $postId;

        if (!$like->save(false)) {
            throw new RuntimeException('Failed to like post.');
        }
    }

    public function unlikePost(int $postId): void
    {
        $like = Like::findByPostId($postId);

        if (!$like) {
            return;
        }

        if (!$like->delete()) {
            throw new RuntimeException('Failed to unlike post.');
        }
    }
}
