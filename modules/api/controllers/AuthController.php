<?php

namespace app\modules\api\controllers;

use app\behaviors\LoginRateLimitBehavior;
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
        $behaviors['loginRateLimit'] = [
            'class' => LoginRateLimitBehavior::class,
            'only' => ['login'],
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
                return $this->formatJson(true, null, 'Register success', 201);
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

        $email = strtolower(trim($form->email));

        $key = sprintf('login_fail:%s:%s', Yii::$app->request->userIP, md5($email));

        $user = User::findByEmail($email);

        if (!$user || !$user->validatePassword($form->password)) {

            $attempts = Yii::$app->cache->get($key) ?: 0;

            Yii::$app->cache->set($key, $attempts + 1, 300);

            return $this->formatJson(false, null, 'Invalid credentials', 401);
        }

        Yii::$app->cache->delete($key);

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
