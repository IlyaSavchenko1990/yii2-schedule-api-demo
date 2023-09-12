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
            'date_in' => $this->dateTime()->notNull(),
            'date_end' => $this->dateTime()->notNull(),
            'weight' => $this->integer()->notNull()->defaultValue(0),
            'length' => $this->integer()->notNull()->defaultValue(0)
        ]);

        $this->createIndex(
            'idx-date_in',
            '{{%meetings}}',
            'date_in'
        );

        $this->createIndex(
            'idx-weight',
            '{{%meetings}}',
            'weight'
        );

        $this->createIndex(
            'idx-length',
            '{{%meetings}}',
            'length'
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
