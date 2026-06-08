<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "ai_logs".
 *
 * @property int $id
 * @property int $user_id
 * @property string $action
 * @property int $prompt_size
 * @property int $response_size
 * @property int $status
 * @property int $duration_ms
 * @property string $error_message
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class BaseAiLog extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ai_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'default', 'value' => null],
            [['user_id', 'action', 'prompt_size', 'response_size', 'status', 'duration_ms', 'error_message'], 'required'],
            [['user_id', 'prompt_size', 'response_size', 'status', 'duration_ms'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['action', 'error_message'], 'string', 'max' => 255],
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
            'action' => 'Action',
            'prompt_size' => 'Prompt Size',
            'response_size' => 'Response Size',
            'status' => 'Status',
            'duration_ms' => 'Duration Ms',
            'error_message' => 'Error Message',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
