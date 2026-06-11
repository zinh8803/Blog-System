<?php

namespace app\models;

use app\behaviors\SoftDeleteBehavior;
use app\behaviors\Timestamp;
use app\models\base\BasePost;
use yii\behaviors\SluggableBehavior;
use yii\db\BaseActiveRecord;

class Post extends BasePost
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';

    public function behaviors()
    {
        return [
            Timestamp::class,
            'softDelete' => [
                'class' => SoftDeleteBehavior::class,
                'attribute' => 'deleted_at',
                'isDeletedAttribute' => 'is_deleted',
            ],
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'title',
                'slugAttribute' => 'slug',
                'ensureUnique' => true,
                'immutable' => false,
                'attributes' => [
                    BaseActiveRecord::EVENT_BEFORE_INSERT => 'slug',
                    BaseActiveRecord::EVENT_BEFORE_UPDATE => 'slug',
                ],
            ],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'user_id',
            'category_id',
            'thumbnail_file_id',
            'title',
            'slug',
            'summary',
            'content',
            'status',
            'view_count',
            'is_deleted',
            'published_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    public function extraFields()
    {
        return [
            'tags',
            'comments',
            'likes',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->user_id = \Yii::$app->user->id;
            }
            if (
                $this->status === self::STATUS_PUBLISHED
                && ($this->isAttributeChanged('status') || empty($this->published_at))
            ) {
                $this->published_at = time();
            }
            return true;
        }
        return false;
    }

    public function increaseViewCount(): bool
    {
        return (bool) $this->updateCounters([
            'view_count' => 1,
        ]);
    }

    public static function find()
    {
        return new query\PostQuery(get_called_class());
    }

    public function softDelete(): bool
    {
        return $this->getBehavior('softDelete')->softDelete();
    }

    public function restore(): bool
    {
        return $this->getBehavior('softDelete')->restore();
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
