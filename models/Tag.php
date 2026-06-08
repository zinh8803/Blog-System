<?php

namespace app\models;

use app\models\base\BaseTag;

class Tag extends BaseTag
{
    public function getPostTags()
    {
        return $this->hasMany(PostTag::class, ['tag_id' => 'id']);
    }

    public function getPosts()
    {
        return $this->hasMany(Post::class, ['id' => 'post_id'])
            ->via('postTags');
    }
}
