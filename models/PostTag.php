<?php

namespace app\models;

use app\models\base\BasePostTag;

class PostTag extends BasePostTag
{
    public function Post()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    public function Tag()
    {
        return $this->hasOne(Tag::class, ['id' => 'tag_id']);
    }
}
