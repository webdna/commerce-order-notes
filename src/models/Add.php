<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2018 Kurious Agency
 */

namespace kuriousagency\commerce\ordernotes\models;

use kuriousagency\commerce\ordernotes\OrderNotes;
use craft\elements\User;
use craft\commerce\elements\Order;
use craft\commerce\Plugin as Commerce;

use Craft;
use craft\base\Model;

/**
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Add extends Note
{
    // Public Properties
    // =========================================================================



    // Public Methods
	// =========================================================================
	

	public function getValue($currency=false)
	{
		return '';
	}

	public function getComments()
	{
		$result = $this->comments."\n";
		foreach ($this->getData()->add as $item)
		{
			$result .= "Added: $item->qty x $item->label\n";
		}
		return $result;
	}

	public function afterValidate()
	{
		if (!$this->getData() || !count($this->getData()->add)) {
			$this->addError('add', "Please select a product");
		}
	}

	public function afterSave()
	{
		foreach ($this->getData()->add as $item)
		{
			$purchasableId = $item->id;
			$note = '';
			$options = [];
			$qty = $item->qty;

			$lineItem = Commerce::getInstance()->getLineItems()->resolveLineItem($this->order->id, $purchasableId, $options);

			// New line items already have a qty of one.
			if ($lineItem->id) {
				$lineItem->qty += $qty;
			} else {
				$lineItem->qty = $qty;
			}

			$lineItem->note = $note;
			//$this->order->isCompleted = false;
			$this->order->addLineItem($lineItem);

		}
	}



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderId', 'userId', 'comments', 'type', 'data'], 'required'],
        ];
    }
}
