<?php

namespace app\modules\api\controllers;

use app\models\forms\otp_mail\OtpMailForm;
use Yii;

class MailController extends BaseController
{
    public function actionSendRegisterMail()
    {
        $form = new OtpMailForm();
        $form->load($this->request->bodyParams, '');

        try {
            if (!$form->sendRegisterOtp()) {
                return $this->formatJson(false, $form->getErrors(), 'Validation failed', 422);
            }

            return $this->formatJson(true, [], 'Mail sent successfully');
        } catch (\Throwable $e) {
            Yii::error($e->getMessage(), __METHOD__);

            return $this->formatJson(false, null, Yii::t('app', 'Failed to send mail: {error}', ['error' => $e->getMessage(),]), 500);
        }
    }
}
