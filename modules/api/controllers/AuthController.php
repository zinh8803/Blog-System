<?php

namespace app\modules\api\controllers;

use app\models\forms\auth\AuthForm;
use app\models\User;
use Yii;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\VerbFilter;

class AuthController extends BaseController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'optional' => ['register', 'login'],
        ];
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'register' => ['POST'],
                'login' => ['POST'],
                'logout' => ['POST'],
                'me' => ['GET'],
            ],
        ];
        return $behaviors;
    }

    public function actionRegister()
    {
        $form = new AuthForm([
            'scenario' => AuthForm::SCENARIO_REGISTER,
        ]);

        $form->load(Yii::$app->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $user = new User();
        $user->username = $form->username;
        $user->email = $form->email;
        $user->setPassword($form->password);
        try {
            if ($user->save(false)) {
                $auth = Yii::$app->authManager;
                $role = $auth->getRole('reader');

                if ($role) {
                    $auth->assign($role, $user->id);
                }
                return $this->formatJson(true, [], 'Register success', 201);
            }
        } catch (\Exception $e) {
            return $this->formatJson(false, null, $e->getMessage(), 500);
        }
        return $this->formatJson(false, $user->getErrors(), 'Register failed', 500);
    }

    public function actionLogin()
    {
        $form = new AuthForm([
            'scenario' => AuthForm::SCENARIO_LOGIN,
        ]);
        $form->load(Yii::$app->request->bodyParams, '');

        if (!$form->validate()) {
            return $this->formatJson(false, $form->errors, 'Validation failed', 422);
        }

        $user = User::findByEmail($form->email);

        if (!$user || !$user->validatePassword($form->password)) {
            return $this->formatJson(false, null, 'Invalid credentials', 401);
        }

        return $this->formatJson(true, [
            'user_id' => $user->id,
            'access_token' => $user->access_token,
        ], 'Login success', 200);
    }

    public function actionMe()
    {
        $user = User::findIdentity(Yii::$app->user->id);

        return $this->formatJson(
            true,
            $user,
            'User profile'
        );
    }

    public function actionLogout()
    {
        $user = Yii::$app->user->identity;

        $user->access_token = Yii::$app->security->generateRandomString(64);
        $user->save(false);

        return $this->formatJson(true, null, 'Logout successfully');
    }

}
