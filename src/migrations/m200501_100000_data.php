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
class m200501_100000_data extends Migration
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
        $this->alterColumn('{{%commerce_ordernotes}}', 'data', $this->text());
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200501_100000_data cannot be reverted.\n";
        return false;
    }

    // Protected Methods
    // =========================================================================


}
