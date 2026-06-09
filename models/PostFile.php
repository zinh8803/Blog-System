<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BasePostFile;

class PostFile extends BasePostFile
{
    public function behaviors()
    {
        return [
            Timestamp::class,
        ];
    }

    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }
}
