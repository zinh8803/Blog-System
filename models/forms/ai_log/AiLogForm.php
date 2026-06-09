<?php

namespace app\models\forms\ai_log;

use app\models\AiLog;

class AiLogForm extends AiLog
{
    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_CREATE => [],
            self::SCENARIO_UPDATE => [],
        ];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [

        ]);
    }
}
