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

/**
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class AddTicket extends Add
{
    public function getName()
	{
		return 'Add Ticket';
	}
	
	public function getProperties()
	{
		// available: comments, value, qty, code, email, add
		return ['comments', 'addTickets'];
    }
    
    public function afterValidate()
	{
		if (!$this->getData() || !count($this->getData()->add)) {
			$this->addError('add', "Please select a ticket");
		}
	}

}