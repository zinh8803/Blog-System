<?php

namespace app\models\forms\file;

use yii\base\Model;
use yii\web\UploadedFile;

class FileForm extends Model
{
    public $id;
    /** @var UploadedFile|null */
    public $imageFile;
    public $folder;

    public function rules(): array
    {
        return [
            [['folder', 'imageFile'], 'required'],
            [['folder'], 'string'],
            [['folder'], 'in', 'range' => ['content', 'thumbnail']],
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => ['png', 'jpg', 'jpeg', 'webp'], 'checkExtensionByMimeType' => false, 'maxSize' => 5 * 1024 * 1024,],
        ];
    }
}
