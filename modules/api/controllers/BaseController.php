<?php

namespace app\modules\api\controllers;

use yii\rest\Controller;

class BaseController extends Controller
{
    protected function verbs(): array
    {
        return [
            'index' => ['GET'],
            'view' => ['GET'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    public function formatJson($status = true, $data = [], $message = "", $code = 200): array
    {
        \Yii::$app->response->statusCode = $code;

        return [
            "code" => $code,
            "status" => $status,
            "data" => $data,
            "message" => $message,
        ];
    }
}
