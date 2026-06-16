<?php

namespace app\modules\api\controllers;

use app\models\File;
use yii\filters\auth\HttpBearerAuth;

class FileController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index', 'view'],
        ];
        return $behaviors;
    }

    public function actionCreate()
    {
        $file = \yii\web\UploadedFile::getInstanceByName('imageFile');

        if (!$file) {
            return $this->formatJson(false, null, 'No file uploaded', 400);
        }

        try {
            $url = \Yii::$app->r2->upload($file, 'thumbnail');

            $model = new File();
            $model->created_by = \Yii::$app->user->id;
            $model->original_name = $file->name;
            $model->path = $url['key'];
            $model->url = $url['url'];
            $model->mime_type = $file->type;
            $model->size = $file->size;
            if (!$model->save()) {
                return $this->formatJson(false, null, 'Failed to save file record: ' . implode(', ', $model->getFirstErrors()), 500);
            }

            return $this->formatJson(true, ['url' => $url], 'File uploaded successfully');
        } catch (\Exception $e) {
            return $this->formatJson(false, null, 'File upload failed: ' . $e->getMessage(), 500);
        }
    }

    public function actionUpdate($id)
    {
        $file = \yii\web\UploadedFile::getInstanceByName('imageFile');

        if (!$file) {
            return $this->formatJson(false, null, 'No file uploaded', 400);
        }

        $model = File::findOne($id);
        if (!$model) {
            return $this->formatJson(false, null, 'File not found', 404);
        }

        try {
            $url = \Yii::$app->r2->update($model->path, $file, 'thumbnail');

            $model->original_name = $file->name;
            $model->path = $url['key'];
            $model->url = $url['url'];
            $model->mime_type = $file->type;
            $model->size = $file->size;
            if (!$model->save()) {
                return $this->formatJson(false, null, 'Failed to update file record: ' . implode(', ', $model->getFirstErrors()), 500);
            }

            return $this->formatJson(true, ['url' => $url], 'File updated successfully');
        } catch (\Exception $e) {
            return $this->formatJson(false, null, 'File update failed: ' . $e->getMessage(), 500);
        }
    }
}
