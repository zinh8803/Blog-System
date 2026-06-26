<?php

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseOtpMail;

class OtpMail extends BaseOtpMail
{
    const OTP_MAIL_TYPE_REGISTER = 'register';

    public function behaviors()
    {
        return [
            Timestamp::class,
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->otp = rand(100000, 999999);
                $this->expire = time() + 300;
            }
            return true;
        }
        return false;
    }

    public function isExpired(): bool
    {
        return $this->expire < time();
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function deleteExitsEmailOtp($email)
    {
        $otpMail = self::find()->where(['email' => $email])->one();
        if ($otpMail) {
            $otpMail->delete();
        }
    }
}
