<?php

namespace app\models\forms\comment;

use app\models\Comment;
use app\models\Post;
use RuntimeException;

class CommentForm extends Comment
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';
    public const SCENARIO_UPDATE_STATUS = 'update_status';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => ['post_id', 'parent_id', 'content',],
            self::SCENARIO_UPDATE => ['content'],
            self::SCENARIO_UPDATE_STATUS => ['status'],
        ];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['post_id', 'content'], 'required'],
            [['post_id', 'parent_id'], 'integer'],
            ['parent_id', 'validateParentComment'],
            [['content'], 'string', 'min' => 2, 'max' => 100, 'message' => 'Content must be between 2 and 100 characters.'],
            [['status'], 'in', 'range' => ['visible', 'hidden']],
            ['post_id', 'exist', 'targetClass' => Post::class, 'targetAttribute' => 'id'],
            ['parent_id', 'exist', 'targetClass' => Comment::class, 'targetAttribute' => 'id', 'skipOnEmpty' => true],
        ]);
    }

    public function validateParentComment($attribute)
    {
        if (empty($this->$attribute)) {
            return;
        }

        $parent = Comment::findOne($this->$attribute);

        if (!$parent) {
            $this->addError($attribute, 'Parent comment not found.');
            return;
        }

        if ($parent->parent_id !== null) {
            $this->addError(
                $attribute,
                'Cannot reply to a reply comment.'
            );
        }
    }

    public function createComment(): ?Comment
    {
        if (!$this->validate()) {
            return null;
        }

        $comment = new Comment();
        $comment->setAttributes($this->getAttributes(['post_id', 'parent_id', 'content']), false);

        if (!$comment->save(false)) {
            throw new RuntimeException('Failed to create comment.');
        }

        return $comment;
    }

    public function updateComment(): ?self
    {
        if (!$this->validate()) {
            return null;
        }

        if (!$this->save(false)) {
            throw new RuntimeException('Failed to update comment.');
        }

        return $this;
    }
}
