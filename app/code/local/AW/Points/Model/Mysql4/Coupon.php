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


class AW_Points_Model_Mysql4_Coupon extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('points/coupon', 'coupon_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        if (is_array($object->getData('customer_group_ids'))) {
            $object->setData('customer_group_ids', implode(',', $object->getData('customer_group_ids')));
        }
        if (is_array($object->getData('website_ids'))) {
            $object->setData('website_ids', implode(',', $object->getData('website_ids')));
        }

        /*  from_date  to_date  convert   */
        if (!$object->getFromDate()) {
            $object->setFromDate(Mage::app()->getLocale()->date());
        }
        if ($object->getFromDate() instanceof Zend_Date) {
            $object->setFromDate($object->getFromDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }

        if (!$object->getToDate()) {
            $object->setToDate(new Zend_Db_Expr('NULL'));
        } else {
            if ($object->getToDate() instanceof Zend_Date) {
                $object->setToDate($object->getToDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
            }
        }
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        if ($object->getData('customer_group_ids')) {
            $object->setData('customer_group_ids', explode(',', $object->getData('customer_group_ids')));
        }
        if ($object->getData('website_ids')) {
            $object->setData('website_ids', explode(',', $object->getData('website_ids')));
        }
    }

    protected function _afterDelete(Mage_Core_Model_Abstract $object) {

        /* delete coupon_transactions after coupon deleting  */

        try {
            $adapter = $this->_getWriteAdapter();
            $where = $adapter->quoteInto(array(
                'coupon_id = ?' => $object->getData('coupon_id')
                    ));
            $adapter->delete(
                    $this->getTable('coupon_transaction'), $where
            );
        } catch (Exception $exc) {
            Mage::helper('awcore/logger')->log($this, $exc->getMessage());
        }
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        if (!is_null($object->getData('customer_group_ids'))) {
            if (is_string($object->getData('customer_group_ids'))) {
                $object->setData('customer_group_ids', explode(',', $object->getData('customer_group_ids')));
            }
        } else {
            $object->setData('customer_group_ids', array());
        }

        if (!is_null($object->getData('website_ids'))) {
            if (is_string($object->getData('website_ids'))) {
                $object->setData('website_ids', explode(',', $object->getData('website_ids')));
            }
        } else {
            $object->setData('website_ids', array());
        }
    }

    public function LoadByCouponCode($coupon, $couponCode) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('coupon'))
                ->where('coupon_code = ?', $couponCode)
                ->limit(1);
        $couponId = $this->_getReadAdapter()->fetchCol($select);
        if ($couponId) {
            $coupon->load($couponId);
        }
        return $this;
    }

}