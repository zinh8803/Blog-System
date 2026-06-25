<?php

namespace app\modules\api\controllers;

use Yii;

class MailController extends BaseController
{
    public function actionTestMail()
    {
        try {
            $sent = Yii::$app->mail->sendVerifyEmail(
                'ngoquocvinh2003@gmail.com',
                'vinh',
                '123456'
            );

            return $this->formatJson(true, [
                'sent' => $sent,
            ], 'Mail sent successfully');
        } catch (\Throwable $e) {
            return $this->formatJson(false, [
                'error' => $e->getMessage(),
            ], 'Failed to send mail', 500);
        }
    }
}
