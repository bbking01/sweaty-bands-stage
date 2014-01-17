<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Model_Config_Zoom
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'default',
	            'label' => Mage::helper('shoppersettings')->__('Magento Default')),
            array(
	            'value'=>'cloud_zoom',
	            'label' => Mage::helper('shoppersettings')->__('CloudZoom')),
            array(
	            'value'=>'lightbox',
	            'label' => Mage::helper('shoppersettings')->__('Lightbox')),
        );
    }

}
