<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Points
 * @version    1.6.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Helper_Order extends Mage_Core_Helper_Abstract
{

    const MINIMUM_POSSIBLE_ERROR = 0.000000000001;

    public function calculatePartial($invCredit, $accessModifier)
    {
        $order = $invCredit->getOrder();

        if ($order->getBaseMoneyForPoints() && $order->getMoneyForPoints()) {

            $moneyBaseToReduce = abs($order->getBaseMoneyForPoints());
            $moneyToReduce = abs($order->getMoneyForPoints());
            
            foreach ($invCredit->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if ($orderItem->isDummy()) {
                    continue;
                }

                $orderItemQty = $orderItem->{"get$accessModifier"}();

                if ($orderItemQty) {
                   
                    $itemToSubtotalMultiplier = ($item->getData('base_row_total') +                           
                            $item->getData('base_weee_tax_applied_row_amount')) /
                            $invCredit->getOrder()->getBaseSubtotal();
                  
                    $moneyBaseToReduceItem = $moneyBaseToReduce * $itemToSubtotalMultiplier;
                    $moneyToReduceItem = $moneyToReduce * $itemToSubtotalMultiplier;
 
                    if (($item->getData('base_row_total') + $moneyBaseToReduceItem) < self::MINIMUM_POSSIBLE_ERROR) {
                        $invCredit->setMoneyForPoints($invCredit->getMoneyForPoints() + $item->getData('row_total'));
                        $invCredit->setBaseMoneyForPoints($invCredit->getBaseMoneyForPoints() + 
                                $item->getData('base_row_total'));
                    } else {
                        $invCredit->setGrandTotal($invCredit->getGrandTotal() - $moneyToReduceItem);
                        $invCredit->setBaseGrandTotal($invCredit->getBaseGrandTotal() - $moneyBaseToReduceItem);
                        $invCredit->setMoneyForPoints($moneyToReduceItem + $invCredit->getMoneyForPoints());
                        $invCredit->setBaseMoneyForPoints($moneyBaseToReduceItem + $invCredit->getBaseMoneyForPoints());
                    }
                }
            }
        }
    }

}
