<?php

namespace app\models\forms\auth;

use app\models\OtpMail;
use app\models\User;
use RuntimeException;
use Yii;

class AuthForm extends User
{
    const SCENARIO_REGISTER = 'create';
    const SCENARIO_LOGIN = 'login';
    public $password;
    public $otp;

    public function scenarios()
    {
        return [
            self::SCENARIO_REGISTER => ['username', 'email', 'password', 'otp'],
            self::SCENARIO_LOGIN => ['email', 'password'],
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'password', 'otp'], 'required'],
            ['email', 'email'],
            ['otp', 'validateOtp'],
            ['email', 'unique', 'targetClass' => User::class, 'on' => self::SCENARIO_REGISTER],
            ['username', 'unique', 'targetClass' => User::class, 'on' => self::SCENARIO_REGISTER],
        ];
    }

    public function validateOtp($attribute, $params)
    {
        $otpMail = OtpMail::find()->where(['email' => $this->email, 'otp' => $this->otp])->one();
        if (!$otpMail) {
            $this->addError($attribute, 'Invalid OTP');
            return;
        }

        if ($otpMail->isExpired()) {
            $this->addError($attribute, 'OTP has expired');
        }
    }

    public function registerUser(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->setPassword($this->password);

        if (!$user->save(false)) {
            throw new RuntimeException(Yii::t('app', 'Register failed'));
        }
        OtpMail::deleteAll(['email' => $this->email]);
        $role = Yii::$app->authManager->getRole('reader');

        if ($role) {
            Yii::$app->authManager->assign($role, $user->id);
        }

        return $user;
    }
}
