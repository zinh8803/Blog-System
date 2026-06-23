<?php

use yii\db\Migration;

class m260616_101657_update_index_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx_posts_status_deleted_id', '{{%posts}}', ['status', 'is_deleted', 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_posts_status_deleted_id', '{{%posts}}');
    }


}
