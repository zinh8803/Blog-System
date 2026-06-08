<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ai_logs}}`.
 */
class m260608_033349_create_ai_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%ai_logs}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'action' => $this->string()->notNull(),
            'prompt_size' => $this->integer()->notNull(),
            'response_size' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'duration_ms' => $this->integer()->notNull(),
            'error_message' => $this->string()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->createIndex('idx-ai_logs-user_id', '{{%ai_logs}}', 'user_id');
        $this->createIndex('idx-ai_logs-action', '{{%ai_logs}}', 'action');
        $this->createIndex('idx-ai_logs-status', '{{%ai_logs}}', 'status');
        $this->createIndex('idx-ai_logs-created_at', '{{%ai_logs}}', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%ai_logs}}');
    }
}
