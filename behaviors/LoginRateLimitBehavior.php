<?php

namespace app\behaviors;

use Yii;
use yii\base\ActionFilter;
use yii\web\TooManyRequestsHttpException;

class LoginRateLimitBehavior extends ActionFilter
{
    public int $maxAttempts = 5;
    public int $duration = 60;

    public function beforeAction($action)
    {
        $email = strtolower(trim(Yii::$app->request->bodyParams['email'] ?? ''));

        $key = sprintf('login_fail:%s:%s', Yii::$app->request->userIP, md5($email));

        $attempts = Yii::$app->cache->get($key) ?: 0;

        if ($attempts >= $this->maxAttempts) {
            throw new TooManyRequestsHttpException(
                'Too many login attempts. Please try after 5 minutes.'
            );
        }

        return parent::beforeAction($action);
    }
}
