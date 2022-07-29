<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2018 webdna
 */

namespace webdna\commerce\ordernotes\services;

use webdna\commerce\ordernotes\OrderNotes;
use webdna\commerce\ordernotes\records\Note as NoteRecord;
use webdna\commerce\ordernotes\models\Note as NoteModel;
use webdna\commerce\ordernotes\models\Qty as QtyModel;

use Craft;
use craft\base\Component;
use craft\commerce\elements\Order;
use craft\helpers\Db;
use craft\db\Query;
use craft\events\RegisterComponentTypesEvent;

use craft\commerce\Plugin as Commerce;

/**
 * @author    webdna
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Notes extends Component
{
    private array $_types = [
        \webdna\commerce\ordernotes\models\Note::class,
        \webdna\commerce\ordernotes\models\Email::class,
        \webdna\commerce\ordernotes\models\Manual::class,
        \webdna\commerce\ordernotes\models\Code::class,
        \webdna\commerce\ordernotes\models\Qty::class,
        \webdna\commerce\ordernotes\models\Add::class,
    ];

    /*
        Event::on(Notes::class, Notes::EVENT_REGISTER_NOTE_TYPES, function(RegisterComponentTypesEvent $e) {
            $e->types[] = MyType::class;
        });
    */

    const EVENT_REGISTER_NOTE_TYPES = 'registerNoteTypes';

    // Public Methods
    // =========================================================================

    public function getNotesByOrderId(int $orderId): array
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
            //$class = "webdna\\commerce\\ordernotes\\models\\".ucfirst($result['type']);
            $model = new $result['type']();
            $notes[] = new $model($result);
        }

        return $notes;
    }

    public function getNotes(array $conditions=[]): array
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
            //$class = "webdna\\commerce\\ordernotes\\models\\".ucfirst($result['type']);
            $model = new $result['type']();
            $notes[] = new $model($result);
        }

        return $notes;
    }

    public function getNoteById(int $id): NoteModel
    {
        $result = (new Query())
            ->select(['*'])
            ->from(['{{%commerce_ordernotes}}'])
            ->where(['id' => $id])
            ->one();

        return new NoteModel($result);
    }

    public function saveNote(NoteModel $model): bool
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

    public function updateOrder(Order $order, bool $recalc=true): void
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

    public function deleteNoteById(int $id): void
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

    public function getTypes(Order $order = null): array
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

    public function getAllTypes(): array
    {
        $event = new RegisterComponentTypesEvent([
            'types' => $this->_types
        ]);

        $this->trigger(self::EVENT_REGISTER_NOTE_TYPES, $event);

        return $event->types;
    }
}
