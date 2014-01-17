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


class AW_Points_Model_Summary extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('points/summary');
    }

    public function loadByCustomer($customer) {
        $this->getResource()->loadByCustomer($this, $customer);
        if (!$this->getId() && $customer->getId()) {
            $this
                    ->setCustomerId($customer->getId())
                    ->save();
        }
        return $this;
    }

    public function loadByCustomerID($customerID) {
        $this->getResource()->loadByCustomerID($this, $customerID);
        if (!$this->getId()) {
            $this
                    ->setCustomerId($customerID)
                    ->save();
        }
        return $this;
    }
    
    public function createFromObject(Varien_Object $object)
    {
        try {
            $summary = Mage::getModel('points/summary')
                    ->setCustomerId($object->getEntityId())
                    ->setBalanceUpdateNotification($object->getSubscribedByDefault())
                    ->setPointsExpirationNotification($object->getSubscribedByDefault())
                    ->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        if (!isset($summary) || !$summary->getId()) {
            return false;
        }

        return $summary;
    }

    public function getCustomer() {
        return Mage::getModel('customer/customer')->load($this->getCustomerId());
    }

}
