<?php

namespace app\modules\api;

use Yii;

/**
 * api module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        Yii::$app->set('errorHandler', [
            'class' => \app\components\ApiErrorHandler::class,
        ]);
        Yii::$app->errorHandler->register();
    }
}
