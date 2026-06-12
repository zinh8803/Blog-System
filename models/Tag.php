<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseTag;
use yii\behaviors\SluggableBehavior;
use yii\helpers\Inflector;

class Tag extends BaseTag
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
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },

            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            },
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

    public static function findOrCreateByName(string $name): self
    {
        $slug = Inflector::slug($name);

        $tag = static::findOne(['slug' => $slug]);

        if ($tag) {
            return $tag;
        }

        $tag = new static();
        $tag->name = $name;
        $tag->slug = $slug;
        $tag->save(false);

        return $tag;
    }
}
