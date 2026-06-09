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

    public static function find()
    {
        return new AiLogQuery(get_called_class());
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
