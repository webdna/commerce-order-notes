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
class Note extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
	public $id;
	public $orderId;
	public $userId;
	public $comments;
	public $type;
	public $value;
	public $data;
	public $dateCreated;
	public $dateUpdated;
	public $uid;

	private $_order;

    // Public Methods
	// =========================================================================
	
	public function getUser()
	{
		return User::find()->anyStatus()->id($this->userId)->one();
	}

	public function getOrder()
	{
		if (!$this->_order) {
			$this->_order = Order::findOne($this->orderId);
		}

		return $this->_order;
	}

	public function getTypeName()
	{
		return OrderNotes::$plugin->notes->getTypeName($this->type);
	}

	public function getComments()
	{
		return $this->comments;
	}

	public function getData()
	{
		if (is_array($this->data)) {
			//Craft::dd((object) $this->data);
			$this->data = json_encode($this->data);
			//return (object) $this->data;
		}
		return json_decode($this->data);
	}

	public function getValue($currency=false)
	{
		$value = $this->value;
		
		if ($currency) {
			$currency = Commerce::getInstance()->getPaymentCurrencies()->getPaymentCurrencyByIso($currency);
			$value = Craft::$app->getFormatter()->asCurrency($this->value, $currency, [], [], false);
		}
		
		return $value;
	}

	public function afterValidate()
	{
		foreach ($this->data as $key => $item)
		{
			if ($item == '') {
				$this->addError($key, ucfirst($key)." cannot be blank.");
			}
		}
	}

	public function afterSave()
	{

	}
	

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderId', 'userId', 'comments', 'type'], 'required'],
        ];
    }
}
