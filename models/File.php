<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseFile;
use app\models\query\FileQuery;

class File extends BaseFile
{
    public function behaviors()
    {
        return [
            Timestamp::class,
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_by = \Yii::$app->user->id;
            }
            return true;
        }
        return false;
    }

    public static function find()
    {
        return new FileQuery(get_called_class());
    }

    public function getUser()
    {
        return $this->hasOne(User::class, [
            'id' => 'created_by'
        ]);
    }

    public function getPostFiles()
    {
        return $this->hasMany(PostFile::class, [
            'file_id' => 'id'
        ]);
    }
}
