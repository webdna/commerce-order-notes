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
class Qty extends Note
{
    // Public Properties
    // =========================================================================



    // Public Methods
	// =========================================================================
	public function getName()
	{
		return 'Quantity Adjustment';
	}
	
	public function getProperties()
	{
		// available: comments, value, qty, code, email, add
		return ['comments', 'qty'];
	}

	public function getValue($currency=false)
	{
		return '';
	}

	public function getComments()
	{
		//return '';
		$result = $this->comments."\n";
		foreach ($this->getData()->qty as $item)
		{
			if ($item->values->old != $item->values->new) {
				$result .= $item->label.": ".$item->values->old." => ".$item->values->new."\n";
			}
		}

		return $result;
	}

	public function afterValidate()
	{
		$changed = false;
		//Craft::dd($this->getData()->qty);

		foreach ($this->getData()->qty as $item)
		{
			//Craft::dd($value);
			if ($item->values->old != $item->values->new) {
				$changed = true;
			}
		}

		if (!$changed) {
			$this->addError('qty', "There are no qty changes.");
		}
	}

	public function afterSave()
	{
		foreach ($this->order->lineItems as $lineItem)
		{
			foreach ($this->getData()->qty as $item)
			{
				if ($lineItem->id == $item->id) {
					$qty = $item->values->new;
					$lineItem->qty = $qty;
					if ($qty == 0 || $qty == '') {
						$this->order->removeLineItem($lineItem);
					} else {
						$this->order->addLineItem($lineItem);
					}
				}
			}
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
