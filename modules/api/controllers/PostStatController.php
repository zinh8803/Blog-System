<?php

namespace app\modules\api\controllers;

use app\models\search\PostDailyStatSearch;
use yii\filters\auth\HttpBearerAuth;

class PostStatController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['index'],
        ];

        return $behaviors;
    }

    public function actionIndex()
    {
        $searchModel = new PostDailyStatSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->successPaginate($dataProvider, true, 'Post stats list');
    }
}
