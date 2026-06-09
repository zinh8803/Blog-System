<?php

namespace app\modules\api\controllers;

use app\models\forms\post\PostForm;
use app\models\search\PostSearch;

class PostController extends BaseController
{
    public function actionIndex()
    {
        try {
            $searchModel = new PostSearch();
            $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
            return $this->successPaginate($dataProvider, true, 'Post list');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new \yii\web\NotFoundHttpException($exception->getMessage());
        }

    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model->fields(), 'Post retrieved successfully');
    }

    public function actionCreate()
    {

    }

    public function actionUpdate($id)
    {

    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->softDelete()) {
            return $this->formatJson(true, null, 'Post deleted successfully');
        }
        return $this->formatJson(false, null, 'Failed to delete post', 400);
    }

    public function findModel($id)
    {
        $model = PostForm::findOne($id);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('Post not found');
        }
        return $model;
    }
}
