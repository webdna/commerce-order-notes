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
class Email extends Note
{
    // Public Properties
    // =========================================================================


    // Public Methods
    // =========================================================================
    public function getName(): string
    {
        return 'Change Email';
    }

    public function getProperties(): array
    {
        // available: comments, value, qty, code, email, add
        return ['comments', 'email'];
    }

    public function getValue(string $currency = ''): string
    {
        return '';
    }

    public function afterSave(): void
    {
        $this->getOrder()->email = $this->getData()->email;
    }

    public function afterDelete(): void
    {
        $this->getOrder()->email = $this->getData()->oldEmail;
    }

    public function rules(): array
    {
        return [
            [['orderId', 'userId', 'type', 'data'], 'required'],
        ];
    }
}
