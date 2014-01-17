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


class AW_Points_Model_Mysql4_Invitation extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('points/invitation', 'invitation_id');
        $this->_read = $this->_getReadAdapter();
    }

    /**
     * Get entity id from database by email address
     *
     * @return integer
     */
    public function loadByEmailAndStatus($invitatioin, $emailAddress, $status) {

        $select = $this->_read->select()
                ->from($this->getTable('points/invitation'))
                ->where("email=? ", $emailAddress)
                ->where("status=? ", $status);

        if ($data = $this->_read->fetchRow($select)) {
            $invitatioin->addData($data);
        }
        $this->_afterLoad($invitatioin);
        return $this;
    }

    /**
     * Get entity id from database by email address
     *
     * @return integer
     */
    public function loadByEmailAndStore($invitatioin, $emailAddress, $storeId) {

        $select = $this->_read->select()
                ->from($this->getTable('points/invitation'))
                ->where("email=? ", $emailAddress)
                ->where("store_id=? ", $storeId);

        if ($data = $this->_read->fetchRow($select)) {
            $invitatioin->addData($data);
        }
        $this->_afterLoad($invitatioin);
        return $this;
    }

    /**
     * Load invitation data  from DB by email     
     *
     * @return array
     */
    public function loadByEmail($invitatioin, $emailAddress) {
        $select = $this->_read->select()
                ->from($this->getTable('points/invitation'))
                ->where('email=?', $emailAddress);

        if ($data = $this->_read->fetchRow($select)) {
            $invitatioin->addData($data);
        }
        $this->_afterLoad($invitatioin);
        return $this;
    }

    /**
     * Load invitation data  from DB by protection code
     *
     *
     * @return array
     */
    public function loadByProtectionCode($invitatioin, $protectionCode) {
        $select = $this->_read->select()
                ->from($this->getTable('points/invitation'))
                ->where('protection_code=?', $protectionCode);

        if ($data = $this->_read->fetchRow($select)) {
            $invitatioin->addData($data);
        }
        $this->_afterLoad($invitatioin);
        return $this;
    }

    /**
     * Load invitation data  from DB by customer and emailAddress
     *
     *
     * @return array
     */
    public function loadByCustomerAndEmail($invitatioin, $customer, $emailAddress) {

        $select = $this->_read->select()
                ->from($this->getTable('points/invitation'))
                ->where("email=? ", $emailAddress)
                ->where("customer_id=?", $customer->getId());

        if ($data = $this->_read->fetchRow($select)) {
            $invitatioin->addData($data);
        }
        $this->_afterLoad($invitatioin);
        return $this;
    }

    /**
     * Load invitation data  from DB by referral id
     *
     *
     * @return array
     */
    public function loadByReferralId($invitatioin, $referralId) {

        $select = $this->_read->select()
                ->from($this->getTable('points/invitation'))
                ->where("referral_id=?", $referralId);

        if ($data = $this->_read->fetchRow($select)) {
            $invitatioin->addData($data);
        }
        $this->_afterLoad($invitatioin);
        return $this;
    }

}
