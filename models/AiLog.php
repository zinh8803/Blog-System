<?php

namespace app\models;

use app\models\base\BaseAiLog;

class AiLog extends BaseAiLog
{
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
