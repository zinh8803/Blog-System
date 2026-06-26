<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "otp_mails".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $email
 * @property int|null $otp
 * @property string|null $type
 * @property int|null $expire
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class BaseOtpMail extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'otp_mails';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'otp', 'type', 'expire', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'otp', 'expire', 'created_at', 'updated_at'], 'integer'],
            [['type'], 'string', 'max' => 255],
            [['email'], 'email'],
            [['email'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'email' => Yii::t('app', 'Email'),
            'user_id' => Yii::t('app', 'User ID'),
            'otp' => Yii::t('app', 'Otp'),
            'type' => Yii::t('app', 'Type'),
            'expire' => Yii::t('app', 'Expire'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

}
