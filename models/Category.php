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

    public function fields()
    {
        return [
            'id',
            'name',
            'slug',
            'status',
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },
            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            }
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
