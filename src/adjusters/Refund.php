<?php
/**
 * Promotions plugin for Craft CMS 3.x
 *
 * Adds promotions
 *
 * @link      https://webdna.co.uk
 * @copyright Copyright (c) 2018 webdna
 */

namespace webdna\commerce\ordernotes\adjusters;

use webdna\commerce\ordernotes\OrderNotes;

use Craft;
use craft\base\Component;
use craft\commerce\base\AdjusterInterface;
use craft\commerce\elements\Order;
use craft\commerce\events\DiscountAdjustmentsEvent;
use craft\commerce\helpers\Currency;
use craft\commerce\models\Discount as DiscountModel;
use craft\commerce\models\OrderAdjustment;
use craft\commerce\Plugin as Commerce;
use craft\commerce\records\Discount as DiscountRecord;

/**
 * Discount Adjuster
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class Refund extends Component implements AdjusterInterface
{
    // Constants
    // =========================================================================

    /**
     * The discount adjustment type.
     */
    const ADJUSTMENT_TYPE = 'refund';


    // Properties
    // =========================================================================


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function adjust(Order $order): array
    {
        $adjustments = [];

        foreach ($order->getTransactions() as $transaction)
        {
            if ($transaction->type == 'refund' && $transaction->status == 'success') {
                //Craft::dd($order);
                /*$adjustment = new OrderAdjustment();
                $adjustment->type = self::ADJUSTMENT_TYPE;
                $adjustment->name = $transaction->note;
                $adjustment->orderId = $order->id;
                $adjustment->description = $transaction->note;
                $adjustment->amount = 0-($transaction->amount);

                $adjustments[] = $adjustment;*/
            }
        }

        return $adjustments;
    }
}
