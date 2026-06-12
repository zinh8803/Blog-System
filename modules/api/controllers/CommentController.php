<?php

namespace app\modules\api\controllers;

use app\models\Comment;
use app\models\forms\comment\CommentForm;
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
        try {
            $comments = Comment::find()
                ->where([
                    'post_id' => $postId,
                    'parent_id' => null,
                ])
                ->with([
                    'replies.user',
                ])
                ->all();

            return $this->formatJson(
                true,
                $comments,
                'Comments retrieved successfully'
            );
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Failed to retrieve comments: ' . $exception->getMessage(), 500);
        }

    }

    public function actionCreate()
    {
        $this->checkPermission('comment.create');
        $form = new CommentForm(['scenario' => CommentForm::SCENARIO_CREATE]);
        $form->load(Yii::$app->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $comment = new Comment();
        $comment->setAttributes($form->attributes, false);
        try {
            if ($comment->save(false)) {
                return $this->formatJson(true, $comment, 'Comment created successfully', 201);
            }
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Create failed: ' . $exception->getMessage(), 500);
        }
    }

    public function actionUpdate(int $id)
    {
        $comment = $this->findModel($id);
        $this->checkPermission('comment.updateOwn', ['comment' => $comment]);
        $comment->scenario = CommentForm::SCENARIO_UPDATE;
        $comment->load(Yii::$app->request->bodyParams, '');
        if (!$comment->validate()) {
            return $this->formatJson(false, $comment->errors, 'Validation failed', 422);
        }

        try {
            if ($comment->save(false)) {
                return $this->formatJson(true, $comment, 'Comment updated successfully');
            }
            return $this->formatJson(false, $comment->errors, 'Update failed', 400);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Update failed: ' . $exception->getMessage(), 500);
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
            return $this->formatJson(false, null, 'Delete failed: ' . $exception->getMessage(), 500);
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
