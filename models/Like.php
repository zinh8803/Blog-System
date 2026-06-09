<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseLike;

class Like extends BaseLike
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
}
