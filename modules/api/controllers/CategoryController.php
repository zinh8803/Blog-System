<?php

namespace app\modules\api\controllers;

use app\models\forms\category\CategoryForm;
use app\models\search\CategorySearch;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class CategoryController extends BaseController
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
        $this->checkPermission('category.create');
        $form = new CategoryForm([
            'scenario' => CategoryForm::SCENARIO_CREATE,
        ]);
        $form->load($this->request->bodyParams, '');

        try {
            $model = $form->createCategory();
            if (!$model) {
                return $this->formatJson(false, $form->errors, 'Validation failed', 422);
            }

            return $this->formatJson(true, $model, 'Category created successfully', 201);
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionUpdate(int $id)
    {
        $this->checkPermission('category.update');
        $model = $this->findModel($id);
        $model->scenario = CategoryForm::SCENARIO_UPDATE;

        $model->load($this->request->bodyParams, '');

        try {
            if (!$model->updateCategory()) {
                return $this->formatJson(false, $model->errors, 'Validation failed', 422);
            }

            return $this->formatJson(true, $model, 'Category updated successfully');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, 'Internal server error', 500);
        }
    }


    public function actionDelete(int $id)
    {
        $this->checkPermission('category.delete');
        $model = $this->findModel($id);
        if (!$model->delete()) {
            throw new NotFoundHttpException('Failed to delete category');
        }
        return $this->formatJson(true, null, 'Category deleted successfully');
    }

    public function findModel(int $id): ?CategoryForm
    {
        $model = CategoryForm::find()->where(['id' => $id])->one();
        if (!$model) {
            throw new NotFoundHttpException('Category not found');
        }
        return $model;
    }
}
