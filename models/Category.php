<?php

namespace app\models;
use app\models\base\BaseCategory;

class Category extends BaseCategory
{
    public function find()
    {
        return new query\CategoryQuery(get_called_class());
    }
    public function getPosts()
    {
        return $this->hasMany(Post::class, ['category_id' => 'id']);
    }
}
