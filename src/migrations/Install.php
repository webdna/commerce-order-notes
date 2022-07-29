<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2018 webdna
 */

namespace webdna\commerce\ordernotes\migrations;

use webdna\commerce\ordernotes\OrderNotes;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    webdna
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public string $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables(): bool
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%commerce_ordernotes}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%commerce_ordernotes}}',
                [
                    'id' => $this->primaryKey(),
                    'orderId' => $this->integer()->notNull(),
                    'userId' => $this->integer()->notNull(),
                    'comments' => $this->string()->notNull(),
                    'type' => $this->string(255)->notNull(),
                    'value' => $this->decimal(14, 4)->notNull()->defaultValue(0),
                    'data' => $this->string()->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes(): void
    {
        $this->createIndex(
            $this->db->getIndexName(
                '{{%commerce_ordernotes}}',
                'orderId',
                true
            ),
            '{{%commerce_ordernotes}}',
            'orderId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(
                '{{%commerce_ordernotes}}',
                'userId',
                true
            ),
            '{{%commerce_ordernotes}}',
            'userId',
            false
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys(): void
    {
        $this->addForeignKey(null, '{{%commerce_ordernotes}}', ['userId'], '{{%users}}', ['id'], null, 'CASCADE');
        $this->addForeignKey(null, '{{%commerce_ordernotes}}', ['orderId'], '{{%commerce_orders}}', ['id'], 'CASCADE', 'CASCADE');
    }

    /**
     * @return void
     */
    protected function insertDefaultData(): void
    {
    }

    /**
     * @return void
     */
    protected function removeTables(): void
    {
        $this->dropTableIfExists('{{%commerce_ordernotes}}');
    }
}
