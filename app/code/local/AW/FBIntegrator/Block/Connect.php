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




if (Mage::helper('fbintegrator')->checkVersion('1.4.0.0')) {
    if (!class_exists('AW_Fbintegrator_Block_Connect')) {

        class Blockconnect extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {
            
        }

    }
} else {
    if (!class_exists('AW_Fbintegrator_Block_Connect')) {

        class Blockconnect extends Mage_Core_Block_Template {
            
        }

    }
}

class AW_Fbintegrator_Block_Connect extends Blockconnect {

    public function __construct() {
        $this->setTemplate('fbintegrator/fb_connect.phtml');
        parent::__construct();
    }

    public function _toHtml() {
        $customer = Mage::getSingleton('customer/session');
        if (!Mage::helper('fbintegrator')->checkApp() || $customer->isLoggedIn() || !Mage::helper('fbintegrator')->extEnabled())
            return false;
        $this->setTemplate('fbintegrator/fb_connect.phtml');
        return parent::_toHtml();
    }

}