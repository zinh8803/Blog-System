<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "likes".
 *
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class BaseLike extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'likes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'post_id'], 'required'],
            [['user_id', 'post_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id', 'post_id'], 'unique', 'targetAttribute' => ['user_id', 'post_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'post_id' => 'Post ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
