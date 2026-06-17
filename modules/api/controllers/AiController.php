<?php

namespace app\modules\api\controllers;

use app\models\forms\ai\AiForm;
use Yii;
use yii\filters\auth\HttpBearerAuth;

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
        $this->checkPermission('ai.use');
        $form = new AiForm([
            'scenario' => AiForm::SCENARIO_GENERATE_TITLE,
        ]);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        try {
            $model = Yii::$app->Ai->generateTitle($form->description);
            return $this->formatJson(true, ['titles' => $model], 'Success');

        } catch (\Throwable $e) {

            return $this->formatJson(false, null, 'AI service timeout or unavailable.', 502);
        }
    }

    public function actionGenerateSummary()
    {
        $this->checkPermission('ai.use');

        $form = new AiForm([
            'scenario' => AiForm::SCENARIO_GENERATE_SUMMARY,
        ]);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        try {
            $model = Yii::$app->Ai->generateSummary($form->content);
            return $this->formatJson(true, ['summary' => $model], 'Success');

        } catch (\Throwable $e) {
            return $this->formatJson(false, null, 'AI service timeout or unavailable.', 502);
        }
    }

    public function actionRewrite()
    {
        $this->checkPermission('ai.use');

        $form = new AiForm([
            'scenario' => AiForm::SCENARIO_REWRITE,
        ]);
        $form->load($this->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        try {
            $model = Yii::$app->Ai->rewrite($form->text, $form->instruction);
            return $this->formatJson(true, ['text' => $model], 'Success');

        } catch (\Throwable $e) {

            return $this->formatJson(false, null, 'AI service timeout or unavailable.', 502);
        }
    }
}
