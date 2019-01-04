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
	public $orderId;
	public $userId;
	public $comments;
	public $type;
	public $value;
	public $data;
	public $dateCreated;

    // Public Methods
	// =========================================================================
	
	public function getUser()
	{

	}

	public function getOrder()
	{

	}

	

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orderId', 'userId', 'comments', 'typeId'], 'required'],
        ];
    }
}
