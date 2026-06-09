<?php

namespace app\modules\api\controllers;

use app\models\Category;
use app\models\forms\category\CategoryForm;
use app\models\search\CategorySearch;
use yii\web\NotFoundHttpException;

class CategoryController extends BaseController
{
    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->formatJson(true, $dataProvider, 'Category list');
    }

    public function actionView(int $id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'Category view');
    }

    public function actionCreate()
    {
        $form = new CategoryForm([
            'scenario' => CategoryForm::SCENARIO_CREATE,
        ]);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $model = new Category();
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

    public function actionUpdate(int $id)
    {
        $form = new CategoryForm([
            'scenario' => CategoryForm::SCENARIO_UPDATE,
        ]);
        $form->id = $id;
        $form->load($this->request->bodyParams, '');
        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $model = $this->findModel($id);
        $model->setAttributes($form->getAttributes(['name', 'status', 'slug']), false);

        try {
            if ($model->save(false)) {
                return $this->formatJson(true, $model, 'Category updated successfully');
            }
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionDelete(int $id)
    {
        $model = $this->findModel($id);
        if (!$model->delete()) {
            throw new NotFoundHttpException('Failed to delete category');
        }
        return $this->formatJson(true, null, 'Category deleted successfully');
    }

    public function findModel(int $id): ?Category
    {
        $model = CategoryForm::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('Category not found');
        }
        return $model;
    }
}
