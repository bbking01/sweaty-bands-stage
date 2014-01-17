<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Block_Media extends Mage_Catalog_Block_Product_View_Media
{

    protected function _beforeToHtml()
    {
        if (Mage::getStoreConfig('shoppersettings/images/zoom', Mage::app()->getStore()->getId()) == 'default') {
            return;
        }
        if (Mage::getStoreConfig('shoppersettings/images/zoom', Mage::app()->getStore()->getId()) == 'lightbox') {
            $this->setTemplate('queldorei/lightbox/media.phtml');
        }
        if (Mage::getStoreConfig('shoppersettings/images/zoom', Mage::app()->getStore()->getId()) == 'cloud_zoom'
            && Mage::getStoreConfig('shoppersettings/cloudzoom/enabled', Mage::app()->getStore()->getId())) {
            $this->setTemplate('queldorei/cloudzoom/media.phtml');
        }
        return $this;
    }
}