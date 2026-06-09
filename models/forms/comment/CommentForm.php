<?php

namespace app\models\forms\comment;

use app\models\Comment;
use app\models\Post;

class CommentForm extends Comment
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';
    public const SCENARIO_UPDATE_STATUS = 'update_status';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['post_id', 'content',],
            self::SCENARIO_UPDATE => ['content'],
            self::SCENARIO_UPDATE_STATUS => ['status'],
        ];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['post_id', 'content'], 'required', 'on' => self::SCENARIO_CREATE],
            [['content'], 'required', 'on' => self::SCENARIO_UPDATE],
            [['status'], 'required', 'on' => self::SCENARIO_UPDATE_STATUS],
            [['content'], 'string', 'max' => 1000],
            [['post_id'], 'exist', 'targetClass' => Post::class, 'targetAttribute' => ['post_id' => 'id']],
        ]);
    }
}
