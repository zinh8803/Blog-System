<?php

namespace app\modules\api\controllers;

use app\models\Like;
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

    public function actionToggle($id)
    {
        try {
            $this->checkPermission('like.toggle');
            $like = Like::findByPostId($id);
            if ($like) {
                $like->delete();
                return $this->formatJson(true, ['liked' => false,], 'Unlike successfully');
            }
            $like = new Like();
            $like->post_id = $id;
            $like->save(false);

            return $this->formatJson(true, ['liked' => true,], 'like successfully');
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), __METHOD__);
            return $this->formatJson(false, null, 'Failed to toggle like: ' . $exception->getMessage(), 500);
        }
    }

}
