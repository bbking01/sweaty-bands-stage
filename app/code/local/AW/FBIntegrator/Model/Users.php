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
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_FBIntegrator
 * @version    2.1.4
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */




class AW_FBIntegrator_Model_Users extends Mage_Core_Model_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('fbintegrator/users', 'id');
    }

    public function createUser($fbId, $email, $customerId) {
        $fb = Mage::getModel('fbintegrator/users');
        $fb->setFbId($fbId)
                ->setFbEmail($email)
                ->setCustomerId($customerId)
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->save();
    }

    public function getUser($fbEmail) {

        $websiteId = Mage::app()->getStore()->getWebsiteId();

        $user = $this->getCollection()
                ->addFieldToFilter('fb_email', array("eq" => $fbEmail))
                ->addFieldToFilter('website_id', array("eq" => $websiteId))
                ->getFirstItem();
        return $user;
    }

}