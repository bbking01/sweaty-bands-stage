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




class AW_Fbintegrator_Block_Meta extends Mage_Core_Block_Template {

    public function __construct() {
        $this->setTemplate('fbintegrator/fb_meta.phtml');
        parent::__construct();
    }

    public function _toHtml() {
        $this->setTemplate('fbintegrator/fb_meta.phtml');
        return parent::_toHtml();
    }

    public function getOpenGraphTags() {
        if ($this->getRequest()->getControllerName() == 'product') {
            $product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('id'));
        }
        return array(
            'og:title' => $this->getLayout()->getBlock('head')->getTitle(),
            'og:type' => 'website',
            'og:url' => $this->helper('fbintegrator')->getCurrentUrlWithStorePort(),
            'og:image' => (isset($product) && $product->getId()) ? $product->getImageUrl() : Mage::helper('fbintegrator')->getStoreLogo(),
            'og:description' => htmlspecialchars(strip_tags((isset($product) && $product->getId()) ? $product->getShortDescription() : $this->getLayout()->getBlock('head')->getTitle())),
            'fb:app_id' => Mage::helper('fbintegrator')->getAppKey(),
            'og:locale' => Mage::getBlockSingleton('fbintegrator/fb')->getLocaleCode(),
        );
    }

}