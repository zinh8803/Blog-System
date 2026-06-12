<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseComment;

class Comment extends BaseComment
{
    public function behaviors()
    {
        return [
            Timestamp::class,
        ];
    }

    public function fields()
    {
        return [
            'id',
            'post_id',
            'user_id',
            'content',
            'parent_id',
            'status',
            'replies' => fn() => $this->parent_id === null ? $this->replies : [],
            'created_at' => function () {
                return date('Y-m-d H:i:s', $this->created_at);
            },
            'updated_at' => function () {
                return date('Y-m-d H:i:s', $this->updated_at);
            }
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->user_id = \Yii::$app->user->id;
                $this->status = 'visible';
            }
            return true;
        }
        return false;
    }

    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getParent()
    {
        return $this->hasOne(Comment::class, [
            'id' => 'parent_id'
        ]);
    }

    public function getReplies()
    {
        return $this->hasMany(Comment::class, [
            'parent_id' => 'id'
        ]);
    }
}
