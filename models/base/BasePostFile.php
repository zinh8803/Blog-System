<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "post_files".
 *
 * @property int $id
 * @property int $post_id
 * @property int $file_id
 * @property string $type
 * @property int $sort_order
 * @property string $created_at
 * @property string $updated_at
 */
class BasePostFile extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'post_files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type'], 'default', 'value' => 'content'],
            [['sort_order'], 'default', 'value' => 0],
            [['post_id', 'file_id', 'created_at', 'updated_at'], 'required'],
            [['post_id', 'file_id', 'sort_order'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['type'], 'string', 'max' => 20],
            [['post_id', 'file_id'], 'unique', 'targetAttribute' => ['post_id', 'file_id']],
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
            'file_id' => 'File ID',
            'type' => 'Type',
            'sort_order' => 'Sort Order',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
