<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseComment;

class Comment extends BaseComment
{
    public function behaviors()
    {
        return [
            Timestamp::class,
        ];
    }

    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getParent()
    {
        return $this->hasOne(Comment::class, [
            'id' => 'parent_id'
        ]);
    }

    public function getReplies()
    {
        return $this->hasMany(Comment::class, [
            'parent_id' => 'id'
        ]);
    }
}
