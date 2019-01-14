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
use kuriousagency\commerce\ordernotes\models\Note as NoteModel;
use kuriousagency\commerce\ordernotes\models\Manual as ManualModel;

use Craft;
use craft\web\Controller;
use craft\commerce\Plugin as Commerce;

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
		$this->requireAcceptsJson();

		$request = Craft::$app->getRequest();

		$type = $request->getParam('type');
		$orderId = $request->getParam('orderId');
		$userId = Craft::$app->getUser()->getIdentity()->id;
		$comments = $request->getParam('comments');
		$value = $request->getParam('value', 0);
		$data = $request->getParam('data', []);

		$class = "kuriousagency\\commerce\\ordernotes\\models\\".ucfirst($type);
		$model = new $class();

		$model->type = $type;
		$model->orderId = $orderId;
		$model->userId = $userId;
		$model->comments = $comments;
		$model->value = $value;
		$model->data = $data;
//Craft::dd($model);
		
		$model->validate();

		//Craft::dump($model->getErrors());

		/*if ($model->hasErrors()) {
			return $this->asJson([
				'errors' => $model->getErrors(),
			]);
		}*/

		if (!OrderNotes::$plugin->notes->saveNote($model)) {
			//Craft::dd($model->getErrors());
			return $this->asJson([
				'errors' => $model->getErrors(),
			]);
		}

		$model->afterSave();
//Craft::dd(count($model->order->lineItems));
		$this->_updateOrder($model->order);

		return $this->asJson(['success'=>true]);
	}

	public function actionDelete()
	{
		$this->requirePostRequest();
		$this->requireAcceptsJson();
		
		$id = Craft::$app->getRequest()->getRequiredBodyParam('id');

		if ($id) {
			$note = OrderNotes::$plugin->notes->getNoteById($id);
			OrderNotes::$plugin->notes->deleteNoteById($id);
			$this->_updateOrder($note->order);
			return $this->asJson(['success' => true]);
		}

		return $this->asErrorJson("There was an error deleting the note.");
	}

	

	private function _updateOrder($order)
	{
		//$order = Commerce::getInstance()->getOrders()->getOrderById($orderId);
		//Craft::dd($order->lineItems);
		$order->isCompleted = false;
		Craft::$app->getElements()->saveElement($order, false);
		$order->markAsComplete();
	}
}
