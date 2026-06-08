<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post_files}}`.
 */
class m260608_043152_create_post_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post_files}}', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull(),
            'file_id' => $this->integer()->notNull(),
            'type' => $this->string(20)->notNull()->defaultValue('content'),
            'sort_order' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx-post_files-post_id', '{{%post_files}}', 'post_id');
        $this->createIndex('idx-post_files-file_id', '{{%post_files}}', 'file_id');
        $this->createIndex('idx-post_files-type', '{{%post_files}}', 'type');

        $this->createIndex(
            'idx-post_files-post-file',
            '{{%post_files}}',
            ['post_id', 'file_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%post_files}}');
    }
}
