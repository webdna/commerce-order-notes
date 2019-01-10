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

use Craft;
use craft\base\Component;
use craft\helpers\Db;
use craft\db\Query;

/**
 * @author    Kurious Agency
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Notes extends Component
{
	private $_types = [
		'note' => 'General note',
		'manual' => 'Manual Discount',
		'code' => 'Discount Code',
		/*'qty' => 'Quantity Adjustment',
		'add' => 'Add Product',*/
	];
	
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
			$class = "kuriousagency\\commerce\\ordernotes\\models\\".ucfirst($result['type']);
			$model = new $class();
			$notes[] = new $model($result);
		}
		
		return $notes;
	}

	public function getNotes()
	{
	
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

	public function deleteNoteById($id)
	{
		$record = NoteRecord::findOne($id);

		if ($record) {
			$record->delete();
		}
	}

	public function getTypeName($key)
	{
		if ($key == 'refund') {
			return 'Refund';
		}

		return $this->_types[$key];
	}

	public function getTypes()
	{
		$types = [];

		foreach ($this->_types as $key => $value)
		{
			if ($key == 'note') {
				$types[$key] = $value;

			} elseif (Craft::$app->user->checkPermission('ordernotes_type_'.$key)) {
				$types[$key] = $value;
			}
		}
		
		return $types;
	}

	public function getAllTypes()
	{
		return $this->_types;
	}
}
