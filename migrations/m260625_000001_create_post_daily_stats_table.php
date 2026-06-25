<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post_daily_stats}}`.
 */
class m260625_000001_create_post_daily_stats_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        if ($this->db->schema->getTableSchema('{{%post_daily_stats}}', true) !== null) {
            return;
        }

        $this->createTable('{{%post_daily_stats}}', [
            'id' => $this->primaryKey(),
            'stat_date' => $this->date()->notNull(),
            'total_posts' => $this->integer()->notNull()->defaultValue(0),
            'published_posts' => $this->integer()->notNull()->defaultValue(0),
            'draft_posts' => $this->integer()->notNull()->defaultValue(0),
            'deleted_posts' => $this->integer()->notNull()->defaultValue(0),
            'total_views' => $this->integer()->notNull()->defaultValue(0),
            'total_likes' => $this->integer()->notNull()->defaultValue(0),
            'total_comments' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-post_daily_stats-stat_date', '{{%post_daily_stats}}', 'stat_date', true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        if ($this->db->schema->getTableSchema('{{%post_daily_stats}}', true) === null) {
            return;
        }

        $this->dropTable('{{%post_daily_stats}}');
    }
}
