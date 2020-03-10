<?php
/**
 * Promotions plugin for Craft CMS 3.x
 *
 * Adds promotions
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2018 Kurious Agency
 */

namespace kuriousagency\commerce\ordernotes\adjusters;

use kuriousagency\commerce\ordernotes\OrderNotes;

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
class Manual extends Component implements AdjusterInterface
{
    // Constants
    // =========================================================================

    /**
     * The discount adjustment type.
     */
    const ADJUSTMENT_TYPE = 'discount';


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
        
		foreach (OrderNotes::$plugin->notes->getNotesByOrderId($order->id) as $note)
		{
			$handle = (new \ReflectionClass($note->type))->getShortName();
			if ($handle == 'Manual') {
				$adjustment = new OrderAdjustment();
				$adjustment->type = self::ADJUSTMENT_TYPE;
				$adjustment->name = $note->name;
				$adjustment->orderId = $order->id;
				$adjustment->description = $note->comments;
				$adjustment->amount = 0-($note->value);
		
				$adjustments[] = $adjustment;
			}
		}
        return $adjustments;
    }
}
