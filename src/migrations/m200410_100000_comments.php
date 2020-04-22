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
class m200410_100000_comments extends Migration
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
        $this->alterColumn('{{%commerce_ordernotes}}', 'comments', $this->text());
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
		echo "m200410_100000_comment cannot be reverted.\n";
        return false;
    }

    // Protected Methods
    // =========================================================================


}
