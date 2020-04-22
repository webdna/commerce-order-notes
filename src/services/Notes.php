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
use kuriousagency\commerce\ordernotes\records\Note as NoteRecord;
use kuriousagency\commerce\ordernotes\models\Note as NoteModel;
use kuriousagency\commerce\ordernotes\models\Qty as QtyModel;

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\db\Query;
use craft\events\RegisterComponentTypesEvent;

use craft\commerce\Plugin as Commerce;

/**
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Notes extends Component
{
	private $_types = [
		\kuriousagency\commerce\ordernotes\models\Note::class,
		\kuriousagency\commerce\ordernotes\models\Email::class,
		\kuriousagency\commerce\ordernotes\models\Manual::class,
		\kuriousagency\commerce\ordernotes\models\Code::class,
		\kuriousagency\commerce\ordernotes\models\Qty::class,
		\kuriousagency\commerce\ordernotes\models\Add::class,
	];

	/*
		Event::on(Notes::class, Notes::EVENT_REGISTER_NOTE_TYPES, function(RegisterComponentTypesEvent $e) {
			$e->types[] = MyType::class;
		});
	*/

	const EVENT_REGISTER_NOTE_TYPES = 'registerNoteTypes';
	
	// Public Methods
    // =========================================================================

	public function getNotesByOrderId($orderId)
	{
		$notes = [];
		
		$results = (new Query())
			->select(['*'])
			->from(['{{%commerce_ordernotes}}'])
			->where(['orderId' => $orderId])
			->orderBy('dateUpdated DESC')
			->all();

		foreach ($results as $result)
		{
			//$class = "kuriousagency\\commerce\\ordernotes\\models\\".ucfirst($result['type']);
			$model = new $result['type']();
			$notes[] = new $model($result);
		}
		
		return $notes;
	}

	public function getNotes($conditions=[])
	{
		$notes = [];
		
		$results = (new Query())
			->select(['*'])
			->from(['{{%commerce_ordernotes}}'])
			->orderBy('dateUpdated DESC');
		
		foreach ($conditions as $condition) {
			$results->andWhere($condition);
		}

		foreach ($results->all() as $result)
		{
			//$class = "kuriousagency\\commerce\\ordernotes\\models\\".ucfirst($result['type']);
			$model = new $result['type']();
			$notes[] = new $model($result);
		}
		
		return $notes;
	}

	public function getNoteById($id)
	{
		$result = (new Query())
			->select(['*'])
			->from(['{{%commerce_ordernotes}}'])
			->where(['id' => $id])
			->one();

		return new NoteModel($result);
	}

	public function saveNote($model)
	{
		$model->validate();

		if ($model->hasErrors()) {
			return false;
		}
		
		$record = NoteRecord::findOne($model->id);

		if (!$record) {
			$record = new NoteRecord();
		}

		$record->orderId = $model->orderId;
		$record->userId = $model->userId;
		$record->comments = $model->comments;
		$record->type = $model->type;
		$record->value = $model->value;
		$record->data = $model->data;

		return $record->save();
	}

	public function updateOrder($order, $recalc=true)
	{
		if ($recalc) {
			$orderComplete = $order->isCompleted;
			$orderRecalcMode = $order->getRecalculationMode();
			$order->isCompleted = false;
			$order->setRecalculationMode('adjustmentsOnly');
			if ($order->couponCode) {
				$discount = Commerce::getInstance()->getDiscounts()->getDiscountByCode($order->couponCode);
				if ($discount) {
					Craft::$app->db->createCommand()->delete('craft_commerce_email_discountuses', [
						'email' => $order->email,
						'discountId' => $discount->id,
					])->execute();
				}
			}
			Craft::$app->getElements()->saveElement($order, false);
			if ($orderComplete != $order->isCompleted) {
				$order->isCompleted = true;
				$order->setRecalculationMode($orderRecalcMode);
				Craft::$app->getElements()->saveElement($order, false);
			}
		} else {
			Craft::$app->getElements()->saveElement($order, false);
		}
	}

	public function deleteNoteById($id)
	{
		$record = NoteRecord::findOne($id);

		if ($record) {
			$record->delete();
		}
	}

	/*public function getTypeName($key)
	{
		if ($key == 'refund') {
			return 'Refund';
		}

		return $this->_types[$key];
	}*/

	public function getTypes($order = null)
	{
		$types = [];

		foreach ($this->_types as $type)
		{
			$handle = (new \ReflectionClass($type))->getShortName();
			if ($handle == 'Note') {
				$types[] = $type;

			} elseif (Craft::$app->user->checkPermission('ordernotes_type_'.$handle)) {
				if ($handle == 'Code') {
					if (!$order || ($order && !$order->isPaid)) {
						$types[] = $type;
					}
				} else {
					$types[] = $type;
				}
			}
		}

		$event = new RegisterComponentTypesEvent([
            'types' => $types
        ]);

		$this->trigger(self::EVENT_REGISTER_NOTE_TYPES, $event);
		
		return $event->types;
	}

	public function getAllTypes()
	{
		$event = new RegisterComponentTypesEvent([
            'types' => $this->_types
        ]);

		$this->trigger(self::EVENT_REGISTER_NOTE_TYPES, $event);
		
		return $event->types;
	}
}
