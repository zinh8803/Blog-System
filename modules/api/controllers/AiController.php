<?php

namespace app\modules\api\controllers;

use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class AiController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => [],
        ];
        return $behaviors;
    }

    public function actionGenerateTitle()
    {
        $description = Yii::$app->request->post('description');

        if (!$description) {
            throw new BadRequestHttpException('Description is required.');
        }

        try {
            return $this->formatJson(true, ['titles' => Yii::$app->Ai->generateTitle($description),], 'Success');

        } catch (\Throwable $e) {

            throw new HttpException(502, 'AI service timeout or unavailable.',);
        }
    }

    public function actionGenerateSummary()
    {
        $content = Yii::$app->request->post('content');

        if (!$content) {
            throw new BadRequestHttpException('Content is required.');
        }

        try {
            return $this->formatJson(true, ['summary' => Yii::$app->Ai->generateSummary($content),], 'Success');

        } catch (\Throwable $e) {
            throw new HttpException(502, 'AI service timeout or unavailable.',);
        }
    }

    public function actionRewrite()
    {
        $text = Yii::$app->request->post('text');
        $instruction = Yii::$app->request->post('instruction');

        if (!$text || !$instruction) {
            throw new BadRequestHttpException(
                'Text and instruction are required.'
            );
        }

        try {
            return $this->formatJson(true, ['text' => Yii::$app->Ai->rewrite($text, $instruction),], 'Success');

        } catch (\Throwable $e) {

            throw new HttpException(502, 'AI service timeout or unavailable.',);
        }
    }
}
