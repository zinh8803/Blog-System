<?php

use yii\db\Migration;

class m260610_065525_update_published_at_to_int_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('posts', 'published_at_new', $this->integer()->Null()->defaultValue(null)->after('published_at'));

        $this->execute("UPDATE posts SET published_at_new = UNIX_TIMESTAMP(published_at)");

        $this->dropColumn('posts', 'published_at');

        $this->renameColumn('posts', 'published_at_new', 'published_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('posts', 'published_at_old', $this->dateTime()->Null()->defaultValue(null)->after('published_at'));

        $this->execute("UPDATE posts SET published_at_old = FROM_UNIXTIME(published_at)");

        $this->dropColumn('posts', 'published_at');

        $this->renameColumn('posts', 'published_at_old', 'published_at');
    }

}
