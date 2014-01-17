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


class AW_Points_Model_Rate extends Mage_Core_Model_Abstract {
    const POINTS_TO_CURRENCY = 1;
    const CURRENCY_TO_POINTS = 2;

    protected $_currentCustomer;
    protected $_currentWebsite;

    public function _construct() {
        parent::_construct();
        $this->_init('points/rate');
    }

    public function getWebsite() {
        return Mage::app()->getWebsite($this->getWebsiteId());
    }

    public function getCurrentCustomer() {
        if (!$this->_currentCustomer) {
            $this->_currentCustomer = Mage::getModel('customer/session')->getCustomer();
        }
        return $this->_currentCustomer;
    }

    public function setCurrentCustomer($customer) {
        $this->_currentCustomer = $customer;
        return $this;
    }

    public function getCurrentWebsite() {
        if (!$this->_currentWebsite) {
            $this->_currentWebsite = Mage::app()->getWebsite();
        }
        return $this->_currentWebsite;
    }

    public function setCurrentWebsite($website) {
        $this->_currentWebsite = $website;
        return $this;
    }

    /**
     * Get Rate for direction. If you use this function from the admin, current customer need to be added by
     * using setCurrentCustomer method, current website - using setCurrentWebsite method
     * @param int $direction
     * @return AW_Points_Model_Rate 
     */
    public function loadByDirection($direction) {
        $this->getResource()->loadRateByCustomerWebsiteDirection($this, $this->getCurrentCustomer(), $this->getCurrentWebsite(), $direction);
        return $this;
    }

    /**
     * Exchanges points to money or money to points. If points to money exchanging, currency symbol can be added by setting
     * @param float $amount
     * @throws Exception
     * @return float 
     */
    public function exchange($amount) {
        if (!$this->getPoints() || !$this->getMoney()) {
            throw new Exception(Mage::helper('points')->__('Exchange rates are incorrect'));
        }

        $newAmount = 0;
        if ($this->getDirection() == self::POINTS_TO_CURRENCY) {
            $newAmount = round($amount * $this->getMoney() / $this->getPoints(), 2);
        } else {
            $newAmount = (int) ($amount * $this->getPoints() / $this->getMoney());
        }
        return $newAmount;
    }

    public function getRateText() {
        return Mage::helper('points')->getRateText($this->getDirection(), $this->getPoints(), $this->getMoney());
    }

}
