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


class AW_Points_Model_Api {

    /**
     * Creates new transaction with information about points changes of customer.
     * To add additional params use $additionalData array
     * Example of usage:
     * Mage::getModel('points/api')->addTransaction(-15, 'points_spend_on_order', $customer, $order, array('order_increment_id' => 111000010), array('notice' => Mage::helper('points')->__('My test notice')))
     * How it works:
     * it will subtract 15 points from customer $customer, action will be set as 'points_spend_on_order', comment for this action is
     * 'Spent on order #%s', to change %s to order increment id you must set array('order_increment_id' => 111000010) as $commentParams,
     * to set notice to the aw_points_transaction table you must directly set 'notice' to $additionalData - array('notice' => Mage::helper('points')->__('My test notice'))
     * @param int $amount
     * @param string $action
     * @param Mage_Customer_Model_Customer $customer
     * @param Varien_Object $objectForAction
     * @param array $commentParams
     * @param array $additionalData
     * @return int $transactionId
     */
    public function addTransaction($amount, $action, $customer, $objectForAction = null, $commentParams = array(), $additionalData = array()) {
        $transactionId = 0;
        try {
            $a = AW_Points_Model_Actions_Abstract::getInstance($action, $customer)
                    ->setAmount($amount)
                    ->setObjectForAction($objectForAction)
                    ->setCommentParams($commentParams)
                    ->addTransaction($additionalData);

            $transactionId = $a->getTransaction()->getId();
        } catch (Exception $ex) {
            return 0;
        }
        return $transactionId;
    }

    /**
     * Exchanges money to points. If you want to apply exchange to current customer and website, you can
     * set only $amount variable. If there will be no rate for this website or customer group, Exception will
     * be thrown
     * Example of usage:
     * Mage::getModel('points/api')->changeMoneyToPoints(1000)
     * @param float $amount
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Core_Model_Website $website
     * @throws Exception
     * @return float 
     */
    public function changeMoneyToPoints($amount, $customer = null, $website = null) {
        $rate = Mage::getModel('points/rate');
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $rate->setCurrentCustomer($customer);
        }
        if ($website instanceof Mage_Core_Model_Website) {
            $rate->setCurrentWebsite($website);
        }
        return
                        $rate
                        ->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS)
                        ->exchange($amount);
    }

    /**
     * Exchanges points to points. If you want to apply exchange to current customer and website, you can
     * set only $amount variable. If there will be no rate for this website or customer group, Exception will
     * be thrown.
     * Example of usage:
     * Mage::getModel('points/api')->changePointsToMoney(1000, null, null)
     * @param float $amount
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Core_Model_Website $website
     * @throws Exception
     * @return float
     */
    public function changePointsToMoney($amount, $customer = null, $website = null) {
        $rate = Mage::getModel('points/rate');
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $rate->setCurrentCustomer($customer);
        }
        if ($website instanceof Mage_Core_Model_Website) {
            $rate->setCurrentWebsite($website);
        }
        return
                        $rate
                        ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                        ->exchange($amount);
    }

    /**
     * Get collection of transactions for customer. Collection can be filtered by:
     * balance_change, order_id, action, comment, notice, change_date, expiration_date.
     * Try not to use joins or group statements
     * Examples of usage:
     * Mage::getModel('points/api')->getCustomerTransactions($customer)->addFieldToFilter('order_id', '5');
     * or
     * $collection = Mage::getModel('points/api')->getCustomerTransactions($customer);
     * $collection->getSelect()->where('order_id > 0');
     * @param Mage_Customer_Model_Customer $customer
     * @return AW_Points_Model_Mysql4_Limitation_Collection
     */
    public function getCustomerTransactions($customer) {
        $summary = Mage::getModel('points/summary')->loadByCustomer($customer);
        return Mage::getModel('points/transaction')->getCollection()->addFieldToFilter('summary_id', $summary->getId());
    }

}
