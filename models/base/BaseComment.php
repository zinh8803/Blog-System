<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "comments".
 *
 * @property int $id
 * @property int $post_id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $content
 * @property string|null $status
 * @property string $created_at
 * @property string $updated_at
 */
class BaseComment extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['parent_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'visible'],
            [['post_id', 'user_id', 'content', 'created_at', 'updated_at'], 'required'],
            [['post_id', 'user_id', 'parent_id'], 'integer'],
            [['content'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'user_id' => 'User ID',
            'parent_id' => 'Parent ID',
            'content' => 'Content',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
