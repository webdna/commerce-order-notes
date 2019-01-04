<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2018 Kurious Agency
 */

namespace kuriousagency\commerce\ordernotes\migrations;

use kuriousagency\commerce\ordernotes\OrderNotes;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Kurious Agency
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
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
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
    public function safeDown()
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
    protected function createTables()
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
					'value' => $this->decimal(14, 4)->notNull()->unsigned(),
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
    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName(
                '{{%commerce_ordernotes}}',
                'orderId',
                true
            ),
            '{{%commerce_ordernotes}}',
            'orderId',
            true
		);
		$this->createIndex(
            $this->db->getIndexName(
                '{{%commerce_ordernotes}}',
                'userId',
                true
            ),
            '{{%commerce_ordernotes}}',
            'userId',
            true
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
    protected function addForeignKeys()
    {
		$this->addForeignKey(null, '{{%commerce_ordernotes}}', ['userId'], '{{%users}}', ['id'], null, 'CASCADE');
		$this->addForeignKey(null, '{{%commerce_ordernotes}}', ['orderId'], '{{%commerce_orders}}', ['id'], 'CASCADE', 'CASCADE');
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%commerce_ordernotes}}');
    }
}
