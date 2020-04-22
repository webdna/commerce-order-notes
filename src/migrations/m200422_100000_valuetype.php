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
class m200422_100000_valuetype extends Migration
{
    // Public Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
		$this->alterColumn('{{%commerce_ordernotes}}', 'value', $this->decimal(14,4)->notNull()->defaultValue(0));
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
		echo "m200422_100000_valuetype cannot be reverted.\n";
        return false;
    }

    // Protected Methods
    // =========================================================================


}
