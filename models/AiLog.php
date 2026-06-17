<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseAiLog;
use app\models\query\AiLogQuery;

class AiLog extends BaseAiLog
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
            'user_id',
            'action',
            'prompt_size',
            'response_size',
            'status',
            'duration_ms',
            'error_message',
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

    public static function find()
    {
        return new AiLogQuery(get_called_class());
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
