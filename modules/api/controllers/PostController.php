<?php

namespace app\modules\api\controllers;

use app\models\forms\post\PostForm;
use app\models\Post;
use app\models\PostHandler;
use app\models\search\PostSearch;
use Yii;
use yii\caching\TagDependency;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class PostController extends BaseController
{
    private const CACHE_TAG = 'post';
    private const TAG_CACHE_TAG = 'tag';
    private const CACHE_DURATION = 3600;

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
            $params = $this->request->queryParams;
            $cacheKey = [self::class, 'index', Yii::$app->language, $params];

            $response = Yii::$app->cache->getOrSet($cacheKey, function () use ($params) {
                $searchModel = new PostSearch();
                $dataProvider = $searchModel->search($params);

                return $this->successPaginate($dataProvider, true, 'Post list');
            }, self::CACHE_DURATION, new TagDependency(['tags' => self::CACHE_TAG]));

            Yii::$app->response->statusCode = $response['code'];
            return $response;
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
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
        $cacheKey = [self::class, 'view-by-slug', Yii::$app->language, $slug];
        $data = Yii::$app->cache->get($cacheKey);

        if ($data === false) {
            $post = $this->findModelSlug($slug);
            $post->increaseViewCount();
            $data = $post->toArray([], Post::EAGER_LOAD_RELATIONS);
            Yii::$app->cache->set(
                $cacheKey,
                $data,
                self::CACHE_DURATION,
                new TagDependency(['tags' => self::CACHE_TAG])
            );
        } else {
            Post::updateAllCounters(['view_count' => 1], ['id' => $data['id']]);
            $data['view_count'] = (int) $data['view_count'] + 1;
            Yii::$app->cache->set(
                $cacheKey,
                $data,
                self::CACHE_DURATION,
                new TagDependency(['tags' => self::CACHE_TAG])
            );
        }

        return $this->formatJson(true, $data, 'Post detail');
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
            $this->invalidatePostCache();
            $this->invalidateTagCache();

            return $this->formatJson(true, $model->toArray([], ['tags']), 'Post created successfully', 201);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('post.update', ['post' => $model,]);
        $model->scenario = PostForm::SCENARIO_UPDATE;
        $model->load($this->request->bodyParams, '');

        if (!$model->validate()) {
            return $this->formatJson(false, $model->errors, 'Validation failed', 422);
        }

        try {
            $post = (new PostHandler())->updateFromForm((int) $id, $model);
            $this->invalidatePostCache();
            $this->invalidateTagCache();

            return $this->formatJson(true, $post->toArray([], ['tags']), 'Post updated successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }

    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('post.delete', ['post' => $model,]);
        try {
            if (!$model->softDelete()) {
                return $this->formatJson(false, null, 'Failed to delete post', 400);
            }

            $this->invalidatePostCache();

            return $this->formatJson(true, null, 'Post deleted successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

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

            $this->invalidatePostCache();

            return $this->formatJson(true, $model, 'Post restored successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

            return $this->formatJson(false, null, $exception->getMessage(), 500);
        }
    }

    public function actionForceDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkPermission('post.delete', ['post' => $model,]);
        try {
            (new PostHandler())->forceDeleteById((int) $id);
            $this->invalidatePostCache();

            return $this->formatJson(true, null, 'Post force deleted successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);

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
            ->andWhere(['slug' => $slug])
            ->with(Post::EAGER_LOAD_RELATIONS)
            ->one();
        if (!$model) {
            throw new NotFoundHttpException('Post not found');
        }
        return $model;
    }

    private function invalidatePostCache(): void
    {
        TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
    }

    private function invalidateTagCache(): void
    {
        TagDependency::invalidate(Yii::$app->cache, self::TAG_CACHE_TAG);
    }
}
