<?php

namespace app\models\forms\auth;

use app\models\User;

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
}
