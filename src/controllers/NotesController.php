<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2018 Kurious Agency
 */

namespace kuriousagency\commerce\ordernotes\controllers;

use kuriousagency\commerce\ordernotes\OrderNotes;

use Craft;
use craft\web\Controller;

/**
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class NotesController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = [];

    // Public Methods
    // =========================================================================

	public function actionSave()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();
		
		//create new note model

		//depending on type:

		//- manual discount

		//- discount code

		//- add product
		//- qty adjustment

		//- standard


		//save it
	}

	public function actionDelete()
	{
		//delete note and undo any changes it made.
	}
}
