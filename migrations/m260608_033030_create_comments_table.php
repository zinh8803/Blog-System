<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%comments}}`.
 */
class m260608_033030_create_comments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%comments}}', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer()->defaultValue(null),
            'content' => $this->text()->notNull(),
            'status' => $this->string()->defaultValue('visible'),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-comments-post_id', '{{%comments}}', 'post_id');
        $this->createIndex('idx-comments-user_id', '{{%comments}}', 'user_id');
        $this->createIndex('idx-comments-parent_id', '{{%comments}}', 'parent_id');
        $this->createIndex('idx-comments-status', '{{%comments}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%comments}}');
    }
}
