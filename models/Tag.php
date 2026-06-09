<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseTag;
use yii\behaviors\SluggableBehavior;

class Tag extends BaseTag
{
    public function behaviors()
    {
        return [
            Timestamp::class,
            SluggableBehavior::className() => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
            ],
        ];
    }

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
