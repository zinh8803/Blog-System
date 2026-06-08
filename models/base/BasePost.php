<?php

namespace app\models\base;

use Yii;

/**
 * This is the model class for table "posts".
 *
 * @property int $id
 * @property int|null $category_id
 * @property int|null $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string $content
 * @property string|null $status
 * @property int|null $thumbnail_file_id
 * @property int|null $view_count
 * @property string|null $published_at
 * @property int|null $is_deleted
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
class BasePost extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'posts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'user_id', 'summary', 'thumbnail_file_id', 'published_at', 'deleted_at'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'draft'],
            [['is_deleted'], 'default', 'value' => 0],
            [['category_id', 'user_id', 'thumbnail_file_id', 'view_count', 'is_deleted'], 'integer'],
            [['title', 'slug', 'content', 'created_at', 'updated_at'], 'required'],
            [['summary', 'content'], 'string'],
            [['published_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['title', 'slug', 'status'], 'string', 'max' => 255],
            [['slug'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => 'Category ID',
            'user_id' => 'User ID',
            'title' => 'Title',
            'slug' => 'Slug',
            'summary' => 'Summary',
            'content' => 'Content',
            'status' => 'Status',
            'thumbnail_file_id' => 'Thumbnail File ID',
            'view_count' => 'View Count',
            'published_at' => 'Published At',
            'is_deleted' => 'Is Deleted',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'deleted_at' => 'Deleted At',
        ];
    }

}
