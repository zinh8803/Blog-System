<?php

namespace app\models;

use app\models\base\BaseFile;
use app\models\query\FileQuery;

class File extends BaseFile
{
    public function find()
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
