<?php

namespace app\models\forms\file;

use app\models\File;

class FileForm extends File
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['name', 'path'],
            self::SCENARIO_UPDATE => ['name', 'path'],
        ];
    }

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['name', 'path'], 'required'],
            ['size', 'integer'],
        ]);
    }
}
