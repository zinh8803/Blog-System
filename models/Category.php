<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseCategory;
use yii\behaviors\SluggableBehavior;

class Category extends BaseCategory
{
    public function behaviors()
    {
        return [
            Timestamp::class,
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
            ],
        ];
    }

    public static function find()
    {
        return new query\CategoryQuery(get_called_class());
    }

    public function getPosts()
    {
        return $this->hasMany(Post::class, ['category_id' => 'id']);
    }
}
