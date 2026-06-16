<?php

namespace app\modules\api\controllers;

use app\models\File;
use app\models\forms\file\FileForm;
use app\models\search\FileSearch;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

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

    public function actionIndex()
    {
        $searchModel = new FileSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->successPaginate($dataProvider, true, 'File list');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'File retrieved successfully');
    }

    public function actionCreate()
    {
        $form = new FileForm();

        $form->load($this->request->bodyParams, '');

        $form->imageFile = UploadedFile::getInstanceByName('imageFile');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }
        try {
            $url = \Yii::$app->r2->upload($form->imageFile, $form->folder);

            $model = new File();
            $model->original_name = $form->imageFile->name;
            $model->path = $url['key'];
            $model->url = $url['url'];
            $model->mime_type = $form->imageFile->type;
            $model->size = $form->imageFile->size;
            if (!$model->save(false)) {
                return $this->formatJson(false, null, 'Failed to save file record: ' . implode(', ', $model->getFirstErrors()), 500);
            }

            return $this->formatJson(true, ['url' => $url], 'File uploaded successfully');
        } catch (\Exception $e) {
            return $this->formatJson(false, null, 'File upload failed: ' . $e->getMessage(), 500);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $form = new FileForm();
        $form->id = $id;
        $form->load($this->request->bodyParams, '');
        $form->imageFile = UploadedFile::getInstanceByName('imageFile');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }


        try {
            $url = \Yii::$app->r2->update($model->path, $form->imageFile, $form->folder);

            $model->original_name = $form->imageFile->name;
            $model->path = $url['key'];
            $model->url = $url['url'];
            $model->mime_type = $form->imageFile->type;
            $model->size = $form->imageFile->size;
            if (!$model->save(false)) {
                return $this->formatJson(false, null, 'Failed to update file record: ' . implode(', ', $model->getFirstErrors()), 500);
            }

            return $this->formatJson(true, ['url' => $url], 'File updated successfully');
        } catch (\Exception $e) {
            return $this->formatJson(false, null, 'File update failed: ' . $e->getMessage(), 500);
        }
    }

    public function findModel($id)
    {
        $model = File::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('File not found');
        }
        return $model;
    }
}
