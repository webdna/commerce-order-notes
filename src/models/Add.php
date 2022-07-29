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

/**
 * @author    webdna
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Add extends Note
{
    // Public Properties
    // =========================================================================



    // Public Methods
    // =========================================================================
    public function getName(): string
    {
        return 'Add Product';
    }

    public function getProperties(): array
    {
        // available: comments, value, qty, code, email, add
        return ['comments', 'add'];
    }

    public function getValue(string $currency = ''): string
    {
        return '';
    }

    public function getComments(): string
    {
        $result = $this->comments."\n";
        foreach ($this->getData()->add as $item)
        {
            $result .= "Added: $item->qty x $item->label\n";
        }
        return $result;
    }

    public function afterValidate(): void
    {
        if (!$this->getData() || !count($this->getData()->add)) {
            $this->addError('add', "Please select a product");
        }
    }

    public function afterSave(): void
    {
        foreach ($this->getData()->add as $item)
        {
            $purchasableId = $item->id;
            $note = '';
            $options = [];
            $qty = $item->qty;

            $lineItem = Commerce::getInstance()->getLineItems()->resolveLineItem($this->order->id, $purchasableId, $options);

            // New line items already have a qty of one.
            if ($lineItem->id) {
                $lineItem->qty += $qty;
            } else {
                $lineItem->qty = $qty;
            }

            $lineItem->note = $note;
            //$this->order->isCompleted = false;
            $this->order->addLineItem($lineItem);

        }
    }

    public function rules(): array
    {
        return [
            [['orderId', 'userId', 'comments', 'type', 'data'], 'required'],
        ];
    }
}
