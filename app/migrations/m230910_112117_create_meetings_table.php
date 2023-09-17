<?php

use yii\db\Migration;
use yii\db\mysql\Schema;

/**
 * Handles the creation of table `{{%meetings}}`.
 */
class m230910_112117_create_meetings_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%meetings}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255),
            'state' => $this->string(125)->notNull()->defaultValue('ACTIVE'),
            'date_in' => $this->dateTime()->notNull(),
            'date_end' => $this->dateTime()->notNull()
        ]);

        $this->createIndex(
            'idx-date_in',
            '{{%meetings}}',
            'date_in'
        );

        $this->createIndex(
            'idx-date_end',
            '{{%meetings}}',
            'date_end'
        );

        $this->createIndex(
            'idx-date_in-date_end',
            '{{%meetings}}',
            'date_in,date_end'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%meetings}}');
    }
}
