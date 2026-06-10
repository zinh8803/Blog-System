<?php

namespace app\models\forms\post;

use app\models\Category;
use app\models\File;
use app\models\Post;

class PostForm extends Post
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';
    public $tags = [];
    public bool $hasTagsInput = false;

    public function load($data, $formName = null)
    {
        $scope = $formName === '' ? $data : ($data[$formName ?? $this->formName()] ?? []);
        $this->hasTagsInput = is_array($scope) && array_key_exists('tags', $scope);

        return parent::load($data, $formName);
    }

    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['title', 'summary', 'content', 'status', 'category_id', 'thumbnail_file_id', 'tags'],
            self::SCENARIO_UPDATE => ['title', 'summary', 'content', 'status', 'category_id', 'thumbnail_file_id', 'tags'],
        ];
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules = array_filter($rules, function ($rule) {
            return !isset($rule[1]) || $rule[1] !== 'unique';
        });

        return array_merge($rules, [
            ['category_id', 'exist', 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            ['thumbnail_file_id', 'exist', 'targetClass' => File::class, 'targetAttribute' => ['thumbnail_file_id' => 'id']],
            [['content'], 'string', 'min' => 10, 'max' => 10000],
            ['status', 'in', 'range' => [Post::STATUS_DRAFT, Post::STATUS_PUBLISHED]],
            ['tags', 'default', 'value' => []],
            [['title'], 'unique', 'targetClass' => Post::class, 'on' => self::SCENARIO_CREATE, 'message' => 'title already exists.',],
            [['title'], 'unique', 'targetClass' => Post::class, 'filter' => ['!=', 'id', $this->id],
                'on' => self::SCENARIO_UPDATE, 'message' => 'title already exists.'],
            ['tags', 'each', 'rule' => ['string', 'max' => 50]],
        ]);
    }
}
