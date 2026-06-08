<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%posts}}`.
 */
class m260608_032531_create_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer(),
            'user_id' => $this->integer(),
            'title' => $this->string()->notNull(),
            'slug' => $this->string()->notNull()->unique(),
            'summary' => $this->text(),
            'content' => $this->text()->notNull(),
            'status' => $this->string()->defaultValue('draft'),
            'thumbnail_file_id' => $this->integer(),
            'view_count' => $this->integer()->defaultValue(0),
            'published_at' => $this->dateTime(),
            'is_deleted' => $this->integer()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'deleted_at' => $this->integer(),
        ]);
        $this->createIndex('idx-posts-status', '{{%posts}}', 'status');
        $this->createIndex('idx-posts-thumbnail_file_id', '{{%posts}}', 'thumbnail_file_id');
        $this->createIndex('idx-posts-category_id', '{{%posts}}', 'category_id');
        $this->createIndex('idx-posts-user_id', '{{%posts}}', 'user_id');
        $this->createIndex('idx-posts-published_at', '{{%posts}}', 'published_at');
        $this->createIndex('idx-posts-is_deleted', '{{%posts}}', 'is_deleted');
        $this->createIndex('idx-posts-deleted_at', '{{%posts}}', 'deleted_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%posts}}');
    }
}
