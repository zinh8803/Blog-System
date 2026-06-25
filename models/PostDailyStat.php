<?php

namespace app\models;

use app\models\base\BasePostDailyStat;

class PostDailyStat extends BasePostDailyStat
{
    public function fields()
    {
        return [
            'date' => 'stat_date',
            'total_posts',
            'published_posts',
            'draft_posts',
            'deleted_posts',
            'total_views',
            'total_likes',
            'total_comments',
            'created_at' => fn() => date('Y-m-d H:i:s', $this->created_at),
            'updated_at' => fn() => date('Y-m-d H:i:s', $this->updated_at),
        ];
    }
}
