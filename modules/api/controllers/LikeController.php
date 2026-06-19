<?php

namespace app\modules\api\controllers;

use app\models\forms\like\LikeForm;
use yii\filters\auth\HttpBearerAuth;

class LikeController extends BaseController
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


    public function actionLike($id)
    {
        $this->checkPermission('like.toggle');
        (new LikeForm())->likePost((int) $id);

        return $this->formatJson(true, ['liked' => true], 'Liked');
    }

    public function actionUnlike($id)
    {
        $this->checkPermission('like.toggle');
        (new LikeForm())->unlikePost((int) $id);

        return $this->formatJson(true, ['liked' => false], 'Unliked');
    }

}
