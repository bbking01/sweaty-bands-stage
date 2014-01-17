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
    if (!class_exists('AW_Fbintegrator_Block_Like')) {

        class Block extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface {
            
        }

    }
} else {
    if (!class_exists('AW_Fbintegrator_Block_Like')) {

        class Block extends Mage_Core_Block_Template {
            
        }

    }
}

class AW_Fbintegrator_Block_Like extends Block {

    private $_randomId = 0;

    public function __construct() {
        $this->setTemplate('fbintegrator/fb_like.phtml');
        $this->_randomId = rand(0, 100);

        parent::__construct();
    }

    protected function _toHtml() {
        $this->setTemplate('fbintegrator/fb_like.phtml');
        return parent::_toHtml();
    }

    public function canShow() {
        $helper = Mage::helper('fbintegrator');
        if ($helper->extEnabled() && $helper->likeEnabled()) {
            if (!($this->getNameInLayout() == 'fbintegrator.like-button-on-product-page'
                    && $helper->likePosition() != AW_FBIntegrator_Model_System_Config_Backend_Source_Position::PRODUCT_PAGE_POSITION)) {
                return true;
            }
        }
        else
            return false;
    }

    public function getLikeConf() {
        return Mage::helper('fbintegrator')->likeStyle();
    }

    public function getBlockId() {
        return $this->_randomId;
    }

}