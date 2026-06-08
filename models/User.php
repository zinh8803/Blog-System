<?php

declare(strict_types=1);

namespace app\models;

use app\behaviors\Timestamp;
use app\models\base\BaseUser;
use yii\web\IdentityInterface;

class User extends BaseUser implements IdentityInterface
{
    public function behaviors()
    {
        return [
            Timestamp::class,
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        unset($fields['password_hash'], $fields['auth_key'], $fields['access_token']);
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id): static|null
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null): static|null
    {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername(string $username): static|null
    {
        return static::findOne(['username' => $username]);
    }

    public static function findByEmail(string $email): static|null
    {
        return static::findOne(['email' => $email]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string|null
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return $this->auth_key === $authKey;
    }

    public function generateAccessToken(): void
    {
        $this->access_token = \Yii::$app->security->generateRandomString(64);
    }

    public function generateAuthKey(): void
    {
        $this->auth_key = \Yii::$app->security->generateRandomString(64);
    }

    public function setPassword($password)
    {
        $this->password_hash = \Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword(string $password): bool
    {
        return \Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->auth_key = \Yii::$app->security->generateRandomString();
                $this->access_token = \Yii::$app->security->generateRandomString(64);
            }

            return true;
        }
        return false;
    }

    public function getPosts()
    {
        return $this->hasMany(Post::class, ['user_id' => 'id']);
    }

    public function getAiLogs()
    {
        return $this->hasMany(AiLog::class, ['user_id' => 'id']);
    }

    public function getComments()
    {
        return $this->hasManys(Comment::class, ['user_id' => 'id']);
    }

    public function getLikes()
    {
        return $this->hasMany(Like::class, ['user_id' => 'id']);
    }

    public function getFiles()
    {
        return $this->hasMany(File::class, ['created_by' => 'id']);
    }
}
