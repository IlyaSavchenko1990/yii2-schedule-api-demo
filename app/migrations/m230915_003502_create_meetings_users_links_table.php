<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%meetings_users_links}}`.
 */
class m230915_003502_create_meetings_users_links_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%meetings_users_links}}', [
            'id' => $this->primaryKey(),
            'meeting_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull()
        ]);

        $this->addForeignKey(
            'fk-link-meeting_id',
            '{{%meetings_users_links}}',
            'meeting_id',
            '{{%meetings}}',
            'id',
            'CASCADE'
        );
        
        $this->addForeignKey(
            'fk-link-user_id',
            '{{%meetings_users_links}}',
            'user_id',
            '{{%users}}',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-links-meeting_id-user_id',
            '{{%meetings_users_links}}',
            'meeting_id,user_id',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%meetings_users_links}}');
    }
}
