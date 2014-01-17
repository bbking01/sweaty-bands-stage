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


class AW_Points_Block_Checkout_Cart_Points extends Mage_Core_Block_Template {

    protected $_quote;
    protected $_appliedRules = array();

    public function __construct() {
        $this->_quote = Mage::getModel('checkout/session')->getQuote();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer && $this->_quote && count($this->_quote->getAllItems())) {
            $ruleCollection = Mage::getModel('points/rule')
                    ->getCollection()
                    ->addAvailableFilter()
                    ->addFilterByCustomerGroup($customer->getGroupId())
                    ->addFilterByWebsiteId(Mage::app()->getWebsite()->getId())
                    ->setOrder('priority', Varien_Data_Collection::SORT_ORDER_ASC);

            foreach ($ruleCollection as $rule) {
                if ($rule->checkRule($this->_quote)) {
                    $this->_appliedRules[] = $rule;
                    if ($rule->getStopRules())
                        break;
                }
            }
        }
    }

    public function getAppliedRules() {
        return $this->_appliedRules;
    }

    public function getStaticBlocks($rule) {
        $blocksIds = explode(',', $rule->getStaticBlocksIds());
        $toReturn = array();

        $processor = Mage::getModel('core/email_template_filter');

        if (!Mage::helper('points')->magentoLess14())
            $processor = Mage::helper('cms')->getBlockTemplateProcessor();


        foreach ($blocksIds as $blockId) {
            $toReturn[] = $processor
                    ->filter(Mage::getModel('cms/block')->load($blockId)->getContent());
        }
        return $toReturn;
    }

    public function getPoints() {
        if (!Mage::helper('points/config')->getIsApplyEarnRates()) {
            return $this->getData('points');
        }
        if (is_null($this->getData('points'))) {
            try {
                $pointsSummary = 0;

                /* Ponts amount for the rules */
                foreach ($this->_appliedRules as $rule) {
                    $pointsSummary += $rule->getPointsChange();
                }
                
               $applyBefore = Mage::helper('points/config')->getPointsCollectionOrder() == AW_Points_Helper_Config::BEFORE_TAX;

                if ($applyBefore) {
                    $apply = $this->_quote->getData('base_subtotal_with_discount');
                } else {
                    $baseSubtotal = $this->_quote->getData('base_subtotal_with_discount');
                    if ($this->_quote->isVirtual()) {
                        $taxAmount = $this->_quote->getBillingAddress()->getData('base_tax_amount');
                    } else {
                        $taxAmount = $this->_quote->getShippingAddress()->getData('base_tax_amount');
                    }
                    $apply = $baseSubtotal + $taxAmount;
                }

                $pointsSummary += Mage::getModel('points/rate')
                        ->loadByDirection(AW_Points_Model_Rate::CURRENCY_TO_POINTS)
                        ->exchange($apply);

                if (Mage::helper('points/config')->getMaximumPointsPerCustomer()) {
                    $customersPoints = 0;

                    $customer = Mage::getSingleton('customer/session')->getCustomer();
                    if ($customer)
                        $customersPoints = Mage::getModel('points/summary')->loadByCustomer($customer)->getPoints();

                    if ($pointsSummary + $customersPoints > Mage::helper('points/config')->getMaximumPointsPerCustomer()) {
                        $pointsSummary = Mage::helper('points/config')->getMaximumPointsPerCustomer() - $customersPoints;
                    }
                }
                $this->setData('points', $pointsSummary);
            } catch (Exception $ex) {
                
            }
        }

        return $this->getData('points');
    }

    public function getMoney() {
        if (is_null($this->getData('money'))) {
            $money = 0;
            try {
                $money = Mage::getModel('points/rate')
                        ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                        ->exchange($this->getPoints());
            } catch (Exception $ex) {
                
            }
            $this->setData('money', $money);
        }

        return $this->getData('money');
    }

    public function customerIsGuest() {
        return Mage::getModel('customer/session')->getCustomer()->getId() ? false : true;
    }

}