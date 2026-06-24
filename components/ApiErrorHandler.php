<?php

namespace app\components;

use Yii;
use yii\web\ErrorHandler;
use yii\web\HttpException;

class ApiErrorHandler extends ErrorHandler
{
    protected function renderException($exception)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $code = $exception instanceof HttpException
            ? $exception->statusCode
            : 500;

        Yii::$app->response->statusCode = $code;

        Yii::$app->response->data = [
            'code' => $code,
            'status' => false,
            'data' => null,
            'message' => Yii::t('app', $exception->getMessage() ?: 'Internal Server Error'),
        ];

        Yii::$app->response->send();
    }
}
