<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseLike;

class Like extends BaseLike
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

    public static function findByPostId($postId)
    {
        return self::find()
            ->where(['post_id' => $postId])
            ->andWhere(['user_id' => \Yii::$app->user->id])
            ->one();
    }
}
