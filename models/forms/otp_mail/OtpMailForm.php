<?php

namespace app\models\forms\otp_mail;

use app\jobs\SendMailJob;
use app\models\OtpMail;
use app\models\User;
use Yii;

class OtpMailForm extends OtpMail
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['email'], 'checkExistingEmail'],
        ]);
    }

    public function checkExistingEmail($attribute)
    {
        if (User::find()->where(['email' => $this->email,])->exists()) {
            $this->addError($attribute, 'Email already exists.');
        }
    }

    public function sendRegisterOtp(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->deleteExitsEmailOtp($this->email);
        $this->type = self::OTP_MAIL_TYPE_REGISTER;
        if (!$this->save(false)) {
            return false;
        }

        Yii::$app->queue->push(new SendMailJob([
            'to' => $this->email,
            'subject' => 'Welcome',
            'view' => 'verify-email',
            'params' => [
                'name' => 'user',
                'otp' => $this->otp,
            ],
        ]));

        return true;
    }
}
