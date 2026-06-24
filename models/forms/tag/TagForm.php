<?php

namespace app\models\forms\tag;

use app\models\Tag;
use RuntimeException;
use Yii;

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
        return array_merge(parent::rules(), [
            [['name'], 'unique', 'targetClass' => Tag::class, 'on' => self::SCENARIO_CREATE, 'message' => Yii::t('app', 'Name already exists.'),],

            [['name'], 'unique', 'targetClass' => Tag::class, 'filter' => ['!=', 'id', $this->id],
                'on' => self::SCENARIO_UPDATE, 'message' => Yii::t('app', 'Name already exists.')],
        ]);
    }

    public function createTag(): ?Tag
    {
        if (!$this->validate()) {
            return null;
        }

        $model = new Tag();
        $model->setAttributes($this->getAttributes(['name', 'slug']), false);

        if (!$model->save(false)) {
            throw new RuntimeException(Yii::t('app', 'Failed to create tag.'));
        }

        return $model;
    }

    public function updateTag(): ?self
    {
        if (!$this->validate()) {
            return null;
        }

        if (!$this->save(false)) {
            throw new RuntimeException(Yii::t('app', 'Failed to update tag.'));
        }

        return $this;
    }
}
