<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2018 Kurious Agency
 */

namespace kuriousagency\commerce\ordernotes\services;

use kuriousagency\commerce\ordernotes\OrderNotes;

use Craft;
use craft\base\Component;

/**
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Notes extends Component
{
    // Public Methods
    // =========================================================================

	public function getNotesByOrderId($orderId)
	{
		return [];
	}

	public function getNotes()
	{

	}

	public function getNoteById($id)
	{

	}

	public function saveNote($modal)
	{

	}

	public function deleteNoteById($id)
	{

	}

	public function getTypes()
	{
		return [
			'note' => 'General note',
			'manual' => 'Manual Discount',
			'code' => 'Discount Code',
			'qty' => 'Quantity Adjustment',
			'add' => 'Add Product',
		];
	}
}
