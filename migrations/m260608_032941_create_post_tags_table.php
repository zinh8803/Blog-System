<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%post_tags}}`.
 */
class m260608_032941_create_post_tags_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%post_tags}}', [
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-post_tags-post_id', '{{%post_tags}}', 'post_id');
        $this->createIndex('idx-post_tags-tag_id', '{{%post_tags}}', 'tag_id');
        $this->createIndex('idx-post_tags-post_id_tag_id', '{{%post_tags}}', ['post_id', 'tag_id'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%post_tags}}');
    }
}
