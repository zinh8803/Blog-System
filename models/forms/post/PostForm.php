<?php

namespace app\models\forms\post;

use app\models\Category;
use app\models\Post;

class PostForm extends Post
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['title', 'content', 'status', 'category_id'],
            self::SCENARIO_UPDATE => ['title', 'content', 'status', 'category_id'],
        ];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            ['category_id', 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['title'], 'string', 'max' => 255],
            [['content'], 'string', 'min' => 10, 'max' => 1000]
        ]);
    }
}
