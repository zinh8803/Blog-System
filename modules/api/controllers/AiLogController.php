<?php

namespace app\modules\api\controllers;

use app\models\AiLog;
use app\models\search\AiLogSearch;
use yii\filters\auth\HttpBearerAuth;
use yii\web\NotFoundHttpException;


class AiLogController extends BaseController
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
        $this->checkPermission('aiLog.index');
        $searchModel = new AiLogSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        return $this->successPaginate($dataProvider, true, 'AiLog list');
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $this->formatJson(true, $model, 'AiLog retrieved successfully');
    }

    private function findModel($id)
    {
        $this->checkPermission('aiLog.view');
        $model = AiLog::findOne($id);
        if (!isset($model)) {
            throw new NotFoundHttpException('AiLog not found.');
        }
        return $model;
    }
}
