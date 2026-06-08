<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%files}}`.
 */
class m260608_033240_create_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%files}}', [
            'id' => $this->primaryKey(),
            'original_name' => $this->string()->notNull(),
            'path' => $this->string()->notNull(),
            'url' => $this->string()->notNull(),
            'mime_type' => $this->string()->notNull(),
            'size' => $this->integer()->notNull(),
            'storage' => $this->string()->notNull()->DefaultValue('r2'),
            'created_by' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->createIndex('idx-files-created_by', '{{%files}}', 'created_by');
        $this->createIndex('idx-files-storage', '{{%files}}', 'storage');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%files}}');
    }
}
