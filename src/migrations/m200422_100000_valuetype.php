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
class m200422_100000_valuetype extends Migration
{
    // Public Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): void
    {
        $this->alterColumn('{{%commerce_ordernotes}}', 'value', $this->decimal(14,4)->notNull()->defaultValue(0));
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200422_100000_valuetype cannot be reverted.\n";
        return false;
    }

    // Protected Methods
    // =========================================================================


}
