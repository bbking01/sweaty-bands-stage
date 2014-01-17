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


class AW_Points_Model_Mysql4_Summary extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('points/summary', 'id');
    }

    public function loadByCustomer($summary, $customer) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('customer_id = ?', $customer->getId());
        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $summary->addData($data);
        }
        $this->_afterLoad($summary);
        return $this;
    }

    public function loadByCustomerID($summary, $customerID) {
        $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable())
                ->where('customer_id = ?', $customerID);
        if ($data = $this->_getReadAdapter()->fetchRow($select)) {
            $summary->addData($data);
        }
        $this->_afterLoad($summary);
        return $this;
    }

}
