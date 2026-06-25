<?php

namespace app\components;

use Yii;
use yii\base\Component;

class MailComponent extends Component
{
    public function sendVerifyEmail(
        string $email,
        string $name,
        string $otp
    ): bool
    {
        $result = Yii::$app->mailer
            ->compose('verify-email', [
                'name' => $name,
                'otp' => $otp,
            ])
            ->setFrom([
                $_ENV['MAIL_USERNAME'] ?? 'noreply@example.com' => 'Blog System',
            ])
            ->setTo($email)
            ->setSubject('Email Verification')
            ->send();

        // var_dump($result);
        return $result;
    }
}
