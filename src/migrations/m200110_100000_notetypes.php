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
class m200110_100000_notetypes extends Migration
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
        $this->update('{{%commerce_ordernotes}}', [
            'type' => 'webdna\commerce\ordernotes\models\Note',
        ], [
            'type' => 'note',
        ]);

        $this->update('{{%commerce_ordernotes}}', [
            'type' => 'webdna\commerce\ordernotes\models\Add',
        ], [
            'type' => 'add',
        ]);

        $this->update('{{%commerce_ordernotes}}', [
            'type' => 'webdna\commerce\ordernotes\models\Qty',
        ], [
            'type' => 'qty',
        ]);

        $this->update('{{%commerce_ordernotes}}', [
            'type' => 'webdna\commerce\ordernotes\models\Code',
        ], [
            'type' => 'code',
        ]);

        $this->update('{{%commerce_ordernotes}}', [
            'type' => 'webdna\commerce\ordernotes\models\Manual',
        ], [
            'type' => 'manual',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
    echo "m200110_100000_notetypes cannot be reverted.\n";
        return false;
    }

    // Protected Methods
    // =========================================================================


}
