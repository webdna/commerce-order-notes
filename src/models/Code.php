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

use Craft;
use craft\base\Model;

/**
 * @author    webdna
 * @package   CommerceOrderNotes
 * @since     1.0.0
 */
class Code extends Note
{
    // Public Properties
    // =========================================================================

    // Public Methods
    // =========================================================================
    public function getName(): string
    {
        return 'Discount Code';
    }

    public function getProperties(): array
    {
        // available: comments, value, qty, code, email, add
        return ['comments', 'code'];
    }

    public function getValue(string $currency = ''): string
    {
        return $this->getData()->code;
    }

    public function rules(): array
    {
        return [
            [['orderId', 'userId', 'comments', 'type', 'value', 'data'], 'required'],
        ];
    }
}
