<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Model_Config_Zoom_Position
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'right',
	            'label' => Mage::helper('shoppersettings')->__('Right')),
            array(
	            'value'=>'inside',
	            'label' => Mage::helper('shoppersettings')->__('Inside')),
        );
    }

}
