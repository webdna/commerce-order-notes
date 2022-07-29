<?php
/**
 * Commerce Order Notes plugin for Craft CMS 3.x
 *
 * Add notes to an order, they can also affect price.
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2018 webdna
 */

namespace webdna\commerce\ordernotes\models;

use webdna\commerce\ordernotes\OrderNotes;
use craft\elements\User;
use craft\commerce\elements\Order;
use craft\commerce\Plugin as Commerce;

use Craft;
use craft\base\Model;
use DateTime;

/**
 * @author    webdna
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Note extends Model
{
    // Public Properties
    // =========================================================================

    public ?int $id = null;
    public ?int $orderId = null;
    public ?int $userId = null;
    public ?string $comments = '';
    public ?string $type = '';
    public ?string $value = '';
    public mixed $data = null;
    public ?DateTime $dateCreated = null;
    public ?DateTime $dateUpdated = null;
    public ?string $uid = null;

    private ?Order $_order = null;

    // Public Methods
    // =========================================================================

    public function getUser(): User
    {
        return User::find()->status(null)->id($this->userId)->one();
    }

    public function getOrder(): Order
    {
        if (!$this->_order) {
            if (!$this->orderId) {
                return null;
            }
            $this->_order = Order::findOne($this->orderId);
        }

        return $this->_order;
    }

    public function getName(): string
    {
        return 'Note';
    }

    public function getProperties(): array
    {
        // available: comments, value, qty, code, email, add
        return ['comments'];
    }

    public function getComments(): string
    {
        return $this->comments;
    }

    public function getData(): mixed
    {
        if (is_array($this->data)) {
            //Craft::dd((object) $this->data);
            $this->data = json_encode($this->data);
            //return (object) $this->data;
        }
        return json_decode($this->data);
    }

    public function getValue(string $currency = ''): string
    {
        $value = $this->value;

        if ($currency) {
            $currency = Commerce::getInstance()->getPaymentCurrencies()->getPaymentCurrencyByIso($currency);
            $value = Craft::$app->getFormatter()->asCurrency($this->value, $currency->iso, [], [], false);
        }

        return $value;
    }

    public function afterValidate(): void
    {
        foreach ($this->data as $key => $item)
        {
            if ($item == '') {
                $this->addError($key, ucfirst($key)." cannot be blank.");
            }
        }
    }

    public function afterSave(): void
    {

    }

    public function afterDelete(): void
    {

    }

    public function rules(): array
    {
        return [
            [['orderId', 'userId', 'comments', 'type'], 'required'],
        ];
    }
}
