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


class AW_Points_Model_Mysql4_Rate extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('points/rate', 'id');
    }

    public function loadRateByCustomerWebsiteDirection($rate, $customer, $website, $direction) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('FIND_IN_SET(?, customer_group_ids)', $customer->getGroupId())
                ->where('FIND_IN_SET(?, website_ids)', $website->getId())
                ->where('direction = ?', $direction);

        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $rate->addData($data);
        }
        $this->_afterLoad($rate);
        return $this;
    }

    protected function _checkUniqueFields(Mage_Core_Model_Abstract $objectToSave) {
        foreach ($objectToSave->getCollection()->addFieldToFilter('direction', $objectToSave->getDirection()) as $item) {
            if ($item->getId() == $objectToSave->getId())
                continue;
            $this->_afterLoad($item);
            $customerGroupUniqueFlag = true;
            foreach ($objectToSave->getData('customer_group_ids') as $objectToSaveCustomerGroupId) {
                if (in_array($objectToSaveCustomerGroupId, $item->getData('customer_group_ids'))) {
                    $customerGroupUniqueFlag = false;
                    break;
                }
            }
            $websiteUniqueFlag = true;
            foreach ($objectToSave->getData('website_ids') as $objectToSaveWebsiteId) {
                if (in_array($objectToSaveWebsiteId, $item->getData('website_ids'))) {
                    $websiteUniqueFlag = false;
                    break;
                }
            }
            if (!$customerGroupUniqueFlag && !$websiteUniqueFlag)
                throw new Exception;
        }
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        $this->_checkUniqueFields($object);
        if (is_array($object->getData('customer_group_ids')))
            $object->setData('customer_group_ids', implode(',', $object->getData('customer_group_ids')));
        if (is_array($object->getData('website_ids')))
            $object->setData('website_ids', implode(',', $object->getData('website_ids')));
    }

    protected function _afterSave(Mage_Core_Model_Abstract $object) {
        if ($object->getData('customer_group_ids'))
            $object->setData('customer_group_ids', explode(',', $object->getData('customer_group_ids')));
        if ($object->getData('website_ids'))
            $object->setData('website_ids', explode(',', $object->getData('website_ids')));
    }

    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        if (!is_null($object->getData('customer_group_ids'))) {
            if (is_string($object->getData('customer_group_ids'))) {
                $object->setData('customer_group_ids', explode(',', $object->getData('customer_group_ids')));
            }
        }
        else
            $object->setData('customer_group_ids', array());

        if (!is_null($object->getData('website_ids'))) {
            if (is_string($object->getData('website_ids'))) {
                $object->setData('website_ids', explode(',', $object->getData('website_ids')));
            }
        }
        else
            $object->setData('website_ids', array());
    }

}