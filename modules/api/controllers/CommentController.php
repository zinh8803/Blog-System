<?php

namespace app\modules\api\controllers;

use app\models\forms\comment\CommentForm;
use app\models\search\CommentSearch;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class CommentController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index', 'view', 'by-post'],
        ];
        return $behaviors;
    }

    public function actionByPost(int $postId)
    {
        $searchModel = new CommentSearch();
        try {
            $comments = $searchModel->search(Yii::$app->request->queryParams, $postId);
            return $this->successPaginate($comments, true, 'Comments retrieved successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, Yii::t('app', 'Failed to retrieve comments: {error}', [
                'error' => $exception->getMessage(),
            ]), 500);
        }

    }

    public function actionCreate()
    {
        $this->checkPermission('comment.create');
        $form = new CommentForm(['scenario' => CommentForm::SCENARIO_CREATE]);
        $form->load(Yii::$app->request->bodyParams, '');

        try {
            $comment = $form->createComment();
            if (!$comment) {
                return $this->formatJson(false, $form->errors, 'Validation failed', 422);
            }

            return $this->formatJson(true, $comment, 'Comment created successfully', 201);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, Yii::t('app', 'Create failed: {error}', [
                'error' => $exception->getMessage(),
            ]), 500);
        }
    }

    public function actionUpdate(int $id)
    {
        $comment = $this->findModel($id);
        $this->checkPermission('comment.updateOwn', ['comment' => $comment]);
        $comment->scenario = CommentForm::SCENARIO_UPDATE;
        $comment->load(Yii::$app->request->bodyParams, '');

        try {
            if (!$comment->updateComment()) {
                return $this->formatJson(false, $comment->errors, 'Validation failed', 422);
            }

            return $this->formatJson(true, $comment, 'Comment updated successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, Yii::t('app', 'Update failed: {error}', [
                'error' => $exception->getMessage(),
            ]), 500);
        }
    }

    public function actionDelete(int $id)
    {
        $comment = $this->findModel($id);
        $this->checkPermission('comment.deleteOwn', ['comment' => $comment]);
        try {
            if ($comment->delete()) {
                return $this->formatJson(true, null, 'Comment deleted successfully');
            }
            return $this->formatJson(false, null, 'Failed to delete comment', 400);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, Yii::t('app', 'Delete failed: {error}', [
                'error' => $exception->getMessage(),
            ]), 500);
        }
    }

    public function findModel(int $id)
    {
        $model = CommentForm::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException('Comment not found');
        }
        return $model;
    }
}
