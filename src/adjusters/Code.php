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
use DateTime;

/**
 * Discount Adjuster
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
class Code extends Component implements AdjusterInterface
{
    // Constants
    // =========================================================================

    /**
     * The discount adjustment type.
     */
    const ADJUSTMENT_TYPE = 'discount';

    const EVENT_AFTER_DISCOUNT_ADJUSTMENTS_CREATED = 'afterDiscountAdjustmentsCreated';


    // Properties
    // =========================================================================
    /**
     * @var Order
     */
    private Order $_order;

    /**
     * @var DiscountModel
     */
    private DiscountModel $_discount;

    /**
     * @var float
     */
    private float $_discountTotal = 0;

    /**
     * @var bool
     */
    private bool $_spreadBaseOrderDiscountsToLineItems = false;


    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function adjust(Order $order): array
    {
        $this->_order = $order;

        $adjustments = [];
        $availableDiscounts = [];
        $codes = [];
        $oldCouponCode = $order->couponCode;
        foreach (OrderNotes::$plugin->notes->getNotesByOrderId($order->id) as $note)
        {
            $handle = (new \ReflectionClass($note->type))->getShortName();
            if ($handle == 'Code') {
                $codes[] = $note->getData()->code;
            }
        }

        if (count($codes)) {

            $discounts = Commerce::getInstance()->getDiscounts()->getAllActiveDiscounts();

            foreach ($codes as $code)
            {
                $order->couponCode = $code;
                foreach ($discounts as $discount) {

                    if ($code && (strcasecmp($code, $discount->code) == 0) && Commerce::getInstance()->getDiscounts()->matchOrder($order,$discount)) {
                        $availableDiscounts[] = $discount;
                    }
                }
                foreach ($availableDiscounts as $discount) {
                    $newAdjustments = $this->_getAdjustments($discount);
                    if ($newAdjustments) {
                        array_push($adjustments, ...$newAdjustments);

                        if ($discount->stopProcessing) {
                            break;
                        }
                    }
                }

                if ($this->_spreadBaseOrderDiscountsToLineItems) {
                    // Consolidate order level discounts to line items.
                    $orderLevelDiscountAmount = 0;
                    $discountTotalByLineItem = [];
                    foreach ($adjustments as $key => $adjustment) {
                        if (!$adjustment->getLineItem()) {
                            // Get the value of the order adjustment and remove it
                            $orderLevelDiscountAmount += $adjustment->amount;
                            unset($adjustments[$key]);
                        } else {
                            // line item adjustment
                            $lineItemHashId = spl_object_hash($adjustment->getLineItem());
                            if (!isset($discountTotalByLineItem[$lineItemHashId])) {
                                $discountTotalByLineItem[$lineItemHashId] = 0;
                            }

                            $discountTotalByLineItem[$lineItemHashId] += $adjustment->amount;
                        }
                    }

                    // Will be a negative if there is a base discount amount off the order.
                    if ($orderLevelDiscountAmount < 0) {
                        foreach ($this->_order->getLineItems() as $lineItem) {
                            $lineItemHashId = spl_object_hash($lineItem);
                            $amount = 0;
                            if (isset($discountTotalByLineItem[$lineItemHashId]) && $orderLevelDiscountAmount < 0) {

                                $priceAfterDiscounts = $lineItem->getSubtotal() + $discountTotalByLineItem[$lineItemHashId];

                                if ($priceAfterDiscounts <= -$orderLevelDiscountAmount) {
                                    $orderLevelDiscountAmount += $priceAfterDiscounts;
                                    $amount = -$priceAfterDiscounts;
                                } elseif ($priceAfterDiscounts > -$orderLevelDiscountAmount) {
                                    $amount = $orderLevelDiscountAmount;
                                    $orderLevelDiscountAmount = 0;
                                }

                                if ($amount) {
                                    $adjustment = new OrderAdjustment();
                                    $adjustment->type = self::ADJUSTMENT_TYPE;
                                    $adjustment->name = "Discount";
                                    $adjustment->setOrder($this->_order);
                                    $adjustment->setLineItem($lineItem);
                                    $adjustment->description = "Spread Base Description";
                                    $adjustment->sourceSnapshot = [];
                                    $adjustment->amount = $amount;
                                    $adjustments[] = $adjustment;
                                }
                            }
                        }
                    }
                }
            }
        }
        $order->couponCode = $oldCouponCode;
        return $adjustments;
    }

    // Private Methods
    // =========================================================================

    /**
     * @param DiscountModel $discount
     * @return OrderAdjustment
     */
    private function _createOrderAdjustment(DiscountModel $discount): OrderAdjustment
    {
        //preparing model
        $adjustment = new OrderAdjustment();
        $adjustment->type = self::ADJUSTMENT_TYPE;
        $adjustment->name = $discount->name;
        // $adjustment->orderId = $this->_order->id;
        $adjustment->setOrder($this->_order);
        $adjustment->description = $discount->description;
        $snapshot = $discount->toArray();
        $snapshot['discountUseId'] = $discount->id ?? null;
        $adjustment->sourceSnapshot = $snapshot;

        return $adjustment;
    }

    /**
     * @param DiscountModel $discount
     * @return OrderAdjustment[]|false
     */
    private function _getAdjustments(DiscountModel $discount): array|false
    {
        $adjustments = [];

        $this->_discount = $discount;

        $now = new \DateTime();
        $from = $this->_discount->dateFrom;
        $to = $this->_discount->dateTo;
        if (($from && $from > $now) || ($to && $to < $now)) {
            return false;
        }

        //checking items
        $matchingQty = 0;
        $matchingTotal = 0;
        $matchingLineIds = [];
        foreach ($this->_order->getLineItems() as $item) {
            $lineItemHashId = spl_object_hash($item);
            if (Commerce::getInstance()->getDiscounts()->matchLineItem($item, $this->_discount,false)) {
                if (!$this->_discount->allGroups) {
                    $customer = $this->_order->getCustomer();
                    $user = $customer ? $customer->getUser() : null;
                    $userGroups = Commerce::getInstance()->getCustomers()->getUserGroupIdsForUser($user);
                    if ($user && array_intersect($userGroups, $this->_discount->getUserGroupIds())) {
                        $matchingLineIds[] = $lineItemHashId;
                        $matchingQty += $item->qty;
                        $matchingTotal += $item->getSubtotal();
                    }
                } else {
                    $matchingLineIds[] = $lineItemHashId;
                    $matchingQty += $item->qty;
                    $matchingTotal += $item->getSubtotal();
                }
            }
        }

        if (!$matchingQty) {
            return false;
        }

        // Have they entered a max qty?
        if ($this->_discount->maxPurchaseQty > 0 && $matchingQty > $this->_discount->maxPurchaseQty) {
            return false;
        }

        // Reject if they have not added enough matching items
        if ($matchingQty < $this->_discount->purchaseQty) {
            return false;
        }

        // Reject if the matching items values is not enough
        if ($matchingTotal < $this->_discount->purchaseTotal) {
            return false;
        }

        foreach ($this->_order->getLineItems() as $item) {
            $lineItemHashId = spl_object_hash($item);
            if ($matchingLineIds && in_array($lineItemHashId, $matchingLineIds, false)) {
                $adjustment = $this->_createOrderAdjustment($this->_discount);
                $adjustment->setLineItem($item);

                $amountPerItem = Currency::round($this->_discount->perItemDiscount * $item->qty);

                //Default is percentage off already discounted price
                $existingLineItemDiscount = $item->getDiscount();
                $existingLineItemPrice = ($item->getSubtotal() + $existingLineItemDiscount);
                $amountPercentage = Currency::round($this->_discount->percentDiscount * $existingLineItemPrice);

                if ($this->_discount->percentageOffSubject == DiscountRecord::TYPE_ORIGINAL_SALEPRICE) {
                    $amountPercentage = Currency::round($this->_discount->percentDiscount * $item->getSubtotal());
                }

                $adjustment->amount = $amountPerItem + $amountPercentage;


                if ($adjustment->amount != 0) {
                    $this->_discountTotal += $adjustment->amount;
                    $adjustments[] = $adjustment;
                }
            }
        }

        if ($discount->baseDiscount !== null && $discount->baseDiscount != 0) {
            $baseDiscountAdjustment = $this->_createOrderAdjustment($discount);
            $baseDiscountAdjustment->amount = $this->_getBaseDiscountAmount($discount);
            $adjustments[] = $baseDiscountAdjustment;
        }

        // only display adjustment if an amount was calculated
        if (!count($adjustments)) {
            return false;
        }

        // Raise the 'beforeMatchLineItem' event
        $event = new DiscountAdjustmentsEvent([
            'order' => $this->_order,
            'discount' => $discount,
            'adjustments' => $adjustments
        ]);

        $this->trigger(self::EVENT_AFTER_DISCOUNT_ADJUSTMENTS_CREATED, $event);

        if (!$event->isValid) {
            return false;
        }

        return $event->adjustments;
    }

    /**
     * @param DiscountModel $discount
     * @return float|int
     */
    private function _getBaseDiscountAmount(DiscountModel $discount): float
    {
        if ($discount->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_VALUE) {
            return $discount->baseDiscount;
        }

        $total = $this->_order->getItemSubtotal();

        if ($discount->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_TOTAL_DISCOUNTED || $discount->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_ITEMS_DISCOUNTED) {
            $total += $this->_discountTotal;
        }

        if ($discount->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_TOTAL_DISCOUNTED || $discount->baseDiscountType == DiscountRecord::BASE_DISCOUNT_TYPE_PERCENT_TOTAL) {
            $total += $this->_order->getTotalShippingCost();
        }

        return ($total / 100) * $discount->baseDiscount;
    }
}
