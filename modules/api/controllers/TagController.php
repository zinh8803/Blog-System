<?php

namespace app\modules\api\controllers;


use app\models\forms\tag\TagForm;
use app\models\search\TagSearch;
use Yii;
use yii\caching\TagDependency;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;

class TagController extends BaseController
{
    private const CACHE_TAG = 'tag';
    private const POST_CACHE_TAG = 'post';
    private const CACHE_DURATION = 3600;

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
        try {
            $params = $this->request->queryParams;
            $cacheKey = [self::class, 'index', $params];

            $response = Yii::$app->cache->getOrSet($cacheKey, function () use ($params) {
                $searchModel = new TagSearch();
                $dataProvider = $searchModel->search($params);

                return $this->successPaginate($dataProvider, true, 'Tag list');
            }, self::CACHE_DURATION, new TagDependency(['tags' => self::CACHE_TAG]));

            Yii::$app->response->statusCode = $response['code'];
            return $response;
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new \yii\web\NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionView($id)
    {
        try {
            $cacheKey = [self::class, 'view', $id];
            $data = Yii::$app->cache->getOrSet($cacheKey, function () use ($id) {
                return $this->findModel($id)->toArray();
            }, self::CACHE_DURATION, new TagDependency(['tags' => self::CACHE_TAG]));

            return $this->formatJson(true, $data, 'Tag info');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionCreate()
    {
        $this->checkPermission('tag.create');
        $form = new TagForm(['scenario' => TagForm::SCENARIO_CREATE,]);
        $form->load($this->request->bodyParams, '');

        try {
            $model = $form->createTag();
            if (!$model) {
                return $this->formatJson(false, $form->errors, 'Validation failed', 422);
            }

            $this->invalidateTagCache();

            return $this->formatJson(true, $model, 'Category created successfully', 201);
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }
    }

    public function actionUpdate($id)
    {
        $this->checkPermission('tag.update');
        $model = $this->findModel($id);
        $model->scenario = TagForm::SCENARIO_UPDATE;
        $model->load($this->request->bodyParams, '');
        try {
            if (!$model->updateTag()) {
                return $this->formatJson(false, $model->errors, 'Validation failed', 422);
            }

            $this->invalidateTagCache();
            $this->invalidatePostCache();

            return $this->formatJson(true, $model, 'Tag updated successfully');
        } catch (\Throwable $exception) {
            Yii::error($exception->getMessage(), __METHOD__);
            throw new NotFoundHttpException($exception->getMessage());
        }

    }

    public function actionDelete($id)
    {
        $this->checkPermission('tag.delete');
        $model = $this->findModel($id);
        if ($model->delete()) {
            $this->invalidateTagCache();
            $this->invalidatePostCache();

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

    private function invalidateTagCache(): void
    {
        TagDependency::invalidate(Yii::$app->cache, self::CACHE_TAG);
    }

    private function invalidatePostCache(): void
    {
        TagDependency::invalidate(Yii::$app->cache, self::POST_CACHE_TAG);
    }
}
