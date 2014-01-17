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




class AW_FBIntegrator_Block_Wishlist_Share extends Mage_Core_Block_Template {

    public function __construct() {
        parent::__construct();
    }

    public function _toHtml() {

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {

            $email = Mage::getSingleton('customer/session')->getCustomer()->getData('email');
            $fbUser = Mage::getModel('fbintegrator/users')->getUser($email);

            if (
                    $fbUser->getCustomerId()
                    && (Mage::helper('fbintegrator')->extEnabled())
                    && (Mage::helper('fbintegrator')->checkApp())
            ) {
                $this->setTemplate('fbintegrator/wishlist/share.phtml');
                return parent::_toHtml();
            }
        }
        return false;
    }

}