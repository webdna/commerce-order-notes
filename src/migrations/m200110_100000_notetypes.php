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
class m200110_100000_notetypes extends Migration
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
        $this->update('{{%commerce_ordernotes}}', [
			'type' => 'kuriousagency\commerce\ordernotes\models\Note',
		], [
			'type' => 'note',
		]);
		
		$this->update('{{%commerce_ordernotes}}', [
			'type' => 'kuriousagency\commerce\ordernotes\models\Add',
		], [
			'type' => 'add',
		]);

		$this->update('{{%commerce_ordernotes}}', [
			'type' => 'kuriousagency\commerce\ordernotes\models\Qty',
		], [
			'type' => 'qty',
		]);

		$this->update('{{%commerce_ordernotes}}', [
			'type' => 'kuriousagency\commerce\ordernotes\models\Code',
		], [
			'type' => 'code',
		]);

		$this->update('{{%commerce_ordernotes}}', [
			'type' => 'kuriousagency\commerce\ordernotes\models\Manual',
		], [
			'type' => 'manual',
		]);
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
		echo "m200110_100000_notetypes cannot be reverted.\n";
        return false;
    }

    // Protected Methods
    // =========================================================================


}
