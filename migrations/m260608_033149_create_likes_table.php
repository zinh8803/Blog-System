<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%likes}}`.
 */
class m260608_033149_create_likes_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%likes}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'post_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
        $this->createIndex('idx-likes-user_id', '{{%likes}}', 'user_id');
        $this->createIndex('idx-likes-post_id', '{{%likes}}', 'post_id');
        $this->createIndex('idx-likes-user_id_post_id', '{{%likes}}', ['user_id', 'post_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%likes}}');
    }
}
