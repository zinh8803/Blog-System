<?php

namespace app\modules\api\controllers;

use app\models\forms\post\PostForm;
use app\models\Post;
use app\models\PostHandler;
use app\models\search\PostSearch;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class PostController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index', 'view', 'view-by-slug'],
        ];
        return $behaviors;
    }

    public function actionIndex()
    {
        try {
            $searchModel = new PostSearch();
            $dataProvider = $searchModel->search(\Yii::$app->request->queryParams);
            return $this->successPaginate($dataProvider, true, 'Post list');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }

    }

    public function actionTrashAll()
    {
        try {
            $searchModel = new PostSearch();
            $dataProvider = $searchModel->searchTrash(\Yii::$app->request->queryParams);
            return $this->successPaginate($dataProvider, true, 'Post trash list');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionView($id)
    {
        $model = $this->findModel($id, true);
        return $this->formatJson(true, $model, 'Post retrieved successfully');
    }

    public function actionViewBySlug($slug)
    {
        $post = $this->findModelSlug($slug);
        $post->increaseViewCount();
        return $this->formatJson(true, $post, 'Post detail');
    }

    public function actionCreate()
    {
        $this->checkPermission('post.create');
        $form = new PostForm(['scenario' => PostForm::SCENARIO_CREATE]);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        try {
            $model = (new PostHandler())->createFromForm($form);

            return $this->formatJson(true, $model->toArray([], ['tags']), 'Post created successfully', 201);
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('post.updateOwn', ['post' => $model,]);
        $model->scenario = PostForm::SCENARIO_UPDATE;
        $model->load($this->request->bodyParams, '');

        if (!$model->validate()) {
            return $this->formatJson(false, $model->errors, 'Validation failed', 422);
        }

        try {
            $post = (new PostHandler())->updateFromForm((int) $id, $model);

            return $this->formatJson(true, $post->toArray([], ['tags']), 'Post updated successfully');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }

    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('post.deleteOwn', ['post' => $model,]);
        try {
            if (!$model->softDelete()) {
                return $this->formatJson(false, null, 'Failed to delete post', 400);
            }

            return $this->formatJson(true, null, 'Post deleted successfully');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }
    }

    public function actionRestore($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('post.restoreOwn', ['post' => $model,]);
        try {
            if (!$model->restore()) {
                return $this->formatJson(false, null, 'Failed to restore post', 400);
            }

            return $this->formatJson(true, $model, 'Post restored successfully');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }
    }

    public function actionForceDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('post.deleteOwn', ['post' => $model,]);
        try {
            (new PostHandler())->forceDeleteById((int) $id);

            return $this->formatJson(true, null, 'Post force deleted successfully');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }
    }

    public function findModel($id, bool $withRelations = false)
    {
        $query = PostForm::find()
            ->where(['id' => $id]);

        if ($withRelations) {
            $query->with(Post::EAGER_LOAD_RELATIONS);
        }

        $model = $query->one();
        if (!$model) {
            throw new NotFoundHttpException('Post not found');
        }
        return $model;
    }

    public function findModelSlug($slug)
    {
        $model = Post::find()
            ->published()
            ->notDeleted()
            ->where(['slug' => $slug])
            ->with(Post::EAGER_LOAD_RELATIONS)
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('Post not found');
        }
        return $model;
    }
}
