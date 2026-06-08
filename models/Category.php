<?php

namespace app\models;
use app\models\base\BaseCategory;

class Category extends BaseCategory
{
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['category_id' => 'id']);
    }
}
