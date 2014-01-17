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


class AW_Points_Block_Adminhtml_Sales_Order_Create_Billing_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
{
    protected function _toHtml()
    {
        $this->setTemplate('aw_points/sales/order/create/billing/method/form.phtml');
        return parent::_toHtml();
    }

    public function getSummaryForCustomer()
    {
        if (!$this->_summaryForCustomer) {
            $this->_summaryForCustomer = Mage::getModel('points/summary')->loadByCustomer($this->getQuote()->getCustomer());
        }
        return $this->_summaryForCustomer;
    }

    public function getMoneyForPoints() {
        if (!$this->getData('money_for_points')) {
            try {
                $websiteId = Mage::app()->getStore($this->getQuote()->getStoreId())->getWebsiteId();
                $moneyForPoints = Mage::getModel('points/rate')
                        ->setCurrentCustomer($this->getQuote()->getCustomer())
                        ->setCurrentWebsite(Mage::app()->getWebsite($websiteId))
                        ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                        ->exchange($this->getSummaryForCustomer()->getPoints());
                $this->setData('money_for_points', Mage::app()->getStore($this->getQuote()->getStoreId())->convertPrice($moneyForPoints, true));
            } catch (Exception $ex) {

            }
        }
        return $this->getData('money_for_points');
    }

    public function getNeededPoints()
    {
        $amount = $this->getQuote()->getData('base_subtotal_with_discount');
        $neededPoints = 0;
        try {
            $neededPoints = Mage::helper('points')->getNeededPoints($amount, $this->getQuote()->getCustomer(), $this->getQuote()->getStoreId());
        } catch (Exception $ex) {

        }
        return $neededPoints;
    }

    public function getLimitedPoints()
    {
        $sum = $this->getQuote()->getData('base_subtotal_with_discount');
        return Mage::helper('points')->getLimitedPoints($sum, $this->getQuote()->getCustomer(), $this->getQuote()->getStoreId());
    }

    public function pointsSectionAvailable()
    {
        $isAvailable =
                $this->getSummaryForCustomer()->getPoints()
                && $this->getMoneyForPoints()
                && Mage::helper('points')->isAvailableToRedeem($this->getSummaryForCustomer()->getPoints())
                && $this->customerIsRegistered();
        ;
        if (!Mage::helper('points/config')->getCanUseWithCoupon()) {
            $isAvailable = $isAvailable && !$this->getQuote()->getData('coupon_code');
        }
        return $isAvailable;
    }

    public function urlToPointsSave()
    {
        return Mage::getUrl('points_admin/adminhtml_sales_order/savePoints');
    }

    protected function customerIsRegistered() {
        $customer = $this->getQuote()->getCustomer();
        return $customer->getId() > 0;
    }
}