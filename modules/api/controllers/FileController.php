<?php

namespace app\modules\api\controllers;

use app\models\forms\file\FileForm;
use app\models\File;
use app\models\search\FileSearch;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

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
        $this->checkPermission('file.create');
        $form = new FileForm();

        $form->load($this->request->bodyParams, '');

        try {
            $model = $form->createFile();
            if (!$model) {
                return $this->formatJson(false, $form->errors, 'Validation failed', 422);
            }

            return $this->formatJson(true, $model, 'File uploaded successfully');
        } catch (\Exception $e) {
            return $this->formatJson(false, null, 'File upload failed: ' . $e->getMessage(), 500);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('file.updateOwn', ['file' => $model]);
        $form = new FileForm();
        $form->id = $id;
        $form->load($this->request->bodyParams, '');

        try {
            if (!$form->updateFile($model)) {
                return $this->formatJson(false, $form->errors, 'Validation failed', 422);
            }

            return $this->formatJson(true, $model, 'File updated successfully');
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
