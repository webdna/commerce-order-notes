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
class Refund extends Note
{
    // Public Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    public function getName(): string
    {
        return 'Refund';
    }

    public function afterValidate(): void
    {

    }

    public function rules(): array
    {
        return [
            [['orderId', 'userId', 'comments', 'type', 'value'], 'required'],
        ];
    }
}
