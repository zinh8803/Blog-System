<?php

namespace app\models\forms\auth;

use app\models\User;
use RuntimeException;
use Yii;

class AuthForm extends User
{
    const SCENARIO_REGISTER = 'create';
    const SCENARIO_LOGIN = 'login';
    public $password;

    public function scenarios()
    {
        return [
            self::SCENARIO_REGISTER => ['username', 'email', 'password'],
            self::SCENARIO_LOGIN => ['email', 'password'],
        ];
    }

    public function rules(): array
    {
        return [
            [['username', 'email', 'password'], 'required'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::class, 'on' => self::SCENARIO_REGISTER],
            ['username', 'unique', 'targetClass' => User::class, 'on' => self::SCENARIO_REGISTER],
        ];
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

        $role = Yii::$app->authManager->getRole('reader');

        if ($role) {
            Yii::$app->authManager->assign($role, $user->id);
        }

        return $user;
    }
}
