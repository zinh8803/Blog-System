<?php

namespace app\modules\api\controllers;

use yii\data\ActiveDataProvider;
use yii\rest\Controller;
use yii\web\ForbiddenHttpException;

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

    public function formatJson($status = true, $data = [], string $message = '', $code = 200): array
    {
        \Yii::$app->response->statusCode = $code;

        return [
            'code' => $code,
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    }

    protected function successPaginate(ActiveDataProvider $dataProvider, $status = true, string $message = 'Data retrieved successfully', $statusCode = 200): array
    {
        return [
            'code' => $statusCode,
            'status' => $status,
            'data' => $dataProvider->getModels(),
            'message' => $message,
            '_meta' => [
                'total' => $dataProvider->getTotalCount(),
                'page' => $dataProvider->pagination->getPage() + 1,
                'limit' => $dataProvider->pagination->getPageSize(),
                'total_page' => $dataProvider->pagination->getPageCount(),
            ],
        ];
    }

    public function checkPermission($permission, array $param = [])
    {
        if (!\Yii::$app->user->can($permission, $param)) {
            throw new ForbiddenHttpException('You do not have permission to perform this action');
        }
    }

}
