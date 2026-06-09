<?php

namespace app\models\forms\tag;

use app\models\Tag;

class TagForm extends Tag
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['name'],
            self::SCENARIO_UPDATE => ['name'],
        ];
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules = array_filter($rules, function ($rule) {
            return !isset($rule[1]) || $rule[1] !== 'unique';
        });

        return array_merge($rules, [
            [['name'], 'unique', 'targetClass' => Tag::class, 'on' => self::SCENARIO_CREATE, 'message' => 'Name already exists.',],

            [['name'], 'unique', 'targetClass' => Tag::class, 'filter' => ['!=', 'id', $this->id],
                'on' => self::SCENARIO_UPDATE, 'message' => 'Name already exists.'],
        ]);
    }
}
