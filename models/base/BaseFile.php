<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "files".
 *
 * @property int $id
 * @property string $original_name
 * @property string $path
 * @property string $url
 * @property string $mime_type
 * @property int $size
 * @property string $storage
 * @property int|null $created_by
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class BaseFile extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'files';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_by', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['storage'], 'default', 'value' => 'r2'],
            [['original_name', 'path', 'url', 'mime_type', 'size'], 'required'],
            [['size', 'created_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['original_name', 'path', 'url', 'mime_type', 'storage'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'original_name' => 'Original Name',
            'path' => 'Path',
            'url' => 'Url',
            'mime_type' => 'Mime Type',
            'size' => 'Size',
            'storage' => 'Storage',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
