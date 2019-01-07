<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://flipboxfactory.com/software/scorecard/license
 * @link       https://www.flipboxfactory.com/software/scorecard/
 */

namespace flipbox\craft\scorecard\migrations;

use craft\db\Migration;
use craft\records\Element as ElementRecord;
use flipbox\craft\scorecard\records\ElementMetric;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
abstract class AbstractElementMetricMigration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTableIfExists(ElementMetric::tableName());

        return true;
    }

    /**
     * Creates the table(s).
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable(ElementMetric::tableName(), $this->tableAttributes());
    }

    /**
     * The table attributes
     *
     * @return array
     */
    protected function tableAttributes(): array
    {
        return [
            'id' => $this->primaryKey(),
            'parentId' => $this->integer(),
            'elementId' => $this->integer()->notNull(),
            'class' => $this->string()->notNull(),
            'score' => $this->float(4),
            'weight' => $this->float(4)->notNull(),
            'version' => $this->string(50)->notNull(),
            'settings' => $this->text(),
            'dateCalculated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ];
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName(
                ElementMetric::tableName(),
                'elementId'
            ),
            ElementMetric::tableName(),
            'elementId',
            ElementRecord::tableName(),
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(
                ElementMetric::tableName(),
                'parentId'
            ),
            ElementMetric::tableName(),
            'parentId',
            ElementMetric::tableName(),
            'id',
            'CASCADE'
        );
    }
}
