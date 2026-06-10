<?php

namespace app\modules\api\controllers;


use app\models\forms\tag\TagForm;
use app\models\search\TagSearch;
use app\models\Tag;
use yii\web\NotFoundHttpException;

class TagController extends BaseController
{
    public function actionIndex()
    {
        try {
            $searchModel = new TagSearch();
            $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
            return $this->formatJson(true, $dataProvider, 'Tag list');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new \yii\web\NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionView($id)
    {
        try {
            $model = $this->findModel($id);
            return $this->formatJson(true, $model, 'Tag info');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionCreate()
    {
        $form = new TagForm(['scenario' => TagForm::SCENARIO_CREATE,]);
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $model = new Tag();
        $model->setAttributes($form->attributes, false);

        try {
            if ($model->save(false)) {
                return $this->formatJson(true, $model, 'Category created successfully', 201);
            }
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = TagForm::SCENARIO_UPDATE;
        $model->load($this->request->bodyParams, '');
        if (!$model->validate()) {
            return $this->formatJson(false, $model->errors, 'Validation failed', 422);
        }
        try {
            if ($model->save(false)) {
                return $this->formatJson(true, $model, 'Tag updated successfully');
            }
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }

    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->delete()) {
            return $this->formatJson(true, null, 'Tag deleted successfully');
        }
        return $this->formatJson(false, null, 'Failed to delete tag', 400);
    }

    public function findModel($id)
    {
        $model = TagForm::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Tag not found');
        }
        return $model;
    }
}
