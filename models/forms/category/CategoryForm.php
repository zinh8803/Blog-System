<?php

namespace app\models\forms\category;

use app\models\Category;

class CategoryForm extends Category
{
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    public function scenarios()
    {
        return [
            self::SCENARIO_CREATE => ['name', 'status'],
            self::SCENARIO_UPDATE => ['name', 'status'],
        ];
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules = array_filter($rules, function ($rule) {
            return !isset($rule[1]) || $rule[1] !== 'unique';
        });

        return array_merge($rules, [
            [['name'], 'unique', 'targetClass' => Category::class, 'on' => self::SCENARIO_CREATE, 'message' => 'Name already exists.',],
            [['name'], 'unique', 'targetClass' => Category::class, 'filter' => ['!=', 'id', $this->id],
                'on' => self::SCENARIO_UPDATE, 'message' => 'Name already exists.'],
        ]);
    }
}
