<?php

namespace app\models;

use app\models\base\BasePostFile;

class PostFile extends BasePostFile
{
    public function getPost()
    {
        return $this->hasOne(Post::class, ['id' => 'post_id']);
    }

    public function getFile()
    {
        return $this->hasOne(File::class, ['id' => 'file_id']);
    }
}
