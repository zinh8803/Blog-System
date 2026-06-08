<?php

namespace app\models;

use app\models\base\BaseFile;

class File extends BaseFile
{
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
