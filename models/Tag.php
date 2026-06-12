<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseTag;
use RuntimeException;
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
        $tags = static::findOrCreateByNames([$name]);

        if ($tags === []) {
            throw new RuntimeException('Tag name cannot be empty.');
        }

        return $tags[0];
    }

    public static function findOrCreateByNames(array $names): array
    {
        $tagNamesBySlug = [];

        foreach ($names as $name) {
            $name = trim((string) $name);

            if ($name === '') {
                continue;
            }

            $tagNamesBySlug[Inflector::slug($name)] = $name;
        }

        if ($tagNamesBySlug === []) {
            return [];
        }

        $tagsBySlug = static::find()
            ->where(['slug' => array_keys($tagNamesBySlug)])
            ->indexBy('slug')
            ->all();

        $tags = [];

        foreach ($tagNamesBySlug as $slug => $name) {
            $tag = $tagsBySlug[$slug] ?? null;

            if (!$tag) {
                $tag = new static();
                $tag->name = $name;
                $tag->slug = $slug;

                if (!$tag->save(false)) {
                    throw new RuntimeException('Failed to create tag.');
                }
            }

            $tags[] = $tag;
        }

        return $tags;
    }
}
