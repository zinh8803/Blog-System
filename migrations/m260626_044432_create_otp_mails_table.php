<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%otp_mails}}`.
 */
class m260626_044432_create_otp_mails_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%otp_mails}}', [
            'id' => $this->primaryKey(),
            'email' => $this->string()->notNull(),
            'user_id' => $this->integer()->Null(),
            'otp' => $this->integer()->Null(),
            'type' => $this->string()->Null(),
            'expire' => $this->integer()->Null(),
            'created_at' => $this->integer()->Null(),
            'updated_at' => $this->integer()->Null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%otp_mails}}');
    }
}
