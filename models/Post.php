<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BasePost;
use yii\behaviors\SluggableBehavior;

class Post extends BasePost
{
    public function behaviors()
    {
        return [
            Timestamp::class,
            SluggableBehavior::className() => [
                'class' => SluggableBehavior::className(),
                'attribute' => 'title',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
            ],
        ];
    }

    public static function find()
    {
        return new query\PostQuery(get_called_class());
    }

    public function getPostTags()
    {
        return $this->hasMany(PostTag::class, ['post_id' => 'id']);
    }

    public function getTags()
    {
        return $this->hasMany(Tag::class, ['id' => 'tag_id'])
            ->via('postTags');
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getThumbnailFile()
    {
        return $this->hasOne(File::class, [
            'id' => 'thumbnail_file_id'
        ]);
    }

    public function getComments()
    {
        return $this->hasMany(Comment::class, ['post_id' => 'id']);
    }

    public function getLikes()
    {
        return $this->hasMany(Like::class, ['post_id' => 'id']);
    }
}
