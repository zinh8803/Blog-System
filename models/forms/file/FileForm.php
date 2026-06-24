<?php

namespace app\models\forms\file;

use app\models\File;
use RuntimeException;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class FileForm extends Model
{
    public $id;
    /** @var UploadedFile|null */
    public $imageFile;
    public $folder;

    public function beforeValidate(): bool
    {
        if (!$this->imageFile instanceof UploadedFile) {
            $this->imageFile = UploadedFile::getInstanceByName('imageFile');
        }

        return parent::beforeValidate();
    }

    public function rules(): array
    {
        return [
            [['folder', 'imageFile'], 'required'],
            [['folder'], 'string'],
            [['folder'], 'in', 'range' => ['content', 'thumbnail']],
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => ['png', 'jpeg', 'webp'], 'checkExtensionByMimeType' => false, 'maxSize' => 5 * 1024 * 1024,],
        ];
    }

    public function createFile(): ?File
    {
        if (!$this->validate()) {
            return null;
        }

        $url = Yii::$app->get('r2')->upload($this->imageFile, $this->folder);
        $model = new File();
        $this->fillFile($model, $url);

        if (!$model->save(false)) {
            throw new RuntimeException(Yii::t('app', 'Failed to save file record: {errors}', [
                'errors' => implode(', ', $model->getFirstErrors()),
            ]));
        }

        return $model;
    }

    public function updateFile(File $model): ?File
    {
        if (!$this->validate()) {
            return null;
        }

        $url = Yii::$app->get('r2')->update($model->path, $this->imageFile, $this->folder);
        $this->fillFile($model, $url);

        if (!$model->save(false)) {
            throw new RuntimeException(Yii::t('app', 'Failed to update file record: {errors}', [
                'errors' => implode(', ', $model->getFirstErrors()),
            ]));
        }

        return $model;
    }

    private function fillFile(File $model, array $url): void
    {
        $model->original_name = $this->imageFile->name;
        $model->path = $url['key'];
        $model->url = $url['url'];
        $model->mime_type = $this->imageFile->type;
        $model->size = $this->imageFile->size;
    }
}
