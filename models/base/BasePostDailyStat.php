<?php

namespace app\models\base;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "post_daily_stats".
 *
 * @property int $id
 * @property string $stat_date
 * @property int $total_posts
 * @property int $published_posts
 * @property int $draft_posts
 * @property int $deleted_posts
 * @property int $total_views
 * @property int $total_likes
 * @property int $total_comments
 * @property int $created_at
 * @property int $updated_at
 */
class BasePostDailyStat extends ActiveRecord
{
    public static function tableName()
    {
        return 'post_daily_stats';
    }

    public function rules()
    {
        return [
            [['stat_date'], 'required'],
            [['stat_date'], 'date', 'format' => 'php:Y-m-d'],
            [[
                'total_posts',
                'published_posts',
                'draft_posts',
                'deleted_posts',
                'total_views',
                'total_likes',
                'total_comments',
                'created_at',
                'updated_at',
            ], 'integer'],
            [['stat_date'], 'unique'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stat_date' => 'Stat Date',
            'total_posts' => 'Total Posts',
            'published_posts' => 'Published Posts',
            'draft_posts' => 'Draft Posts',
            'deleted_posts' => 'Deleted Posts',
            'total_views' => 'Total Views',
            'total_likes' => 'Total Likes',
            'total_comments' => 'Total Comments',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
