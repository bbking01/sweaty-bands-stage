<?php
/**
 * @version   1.0 12.0.2012
 * @author    Queldorei http://www.queldorei.com <mail@queldorei.com>
 * @copyright Copyright (C) 2010 - 2012 Queldorei
 */

class Queldorei_ShopperSettings_Model_Config_Bg
{

    public function toOptionArray()
    {
        return array(
            array(
	            'value'=>'stretch',
	            'label' => Mage::helper('shoppersettings')->__('stretch')),
            array(
	            'value'=>'tile',
	            'label' => Mage::helper('shoppersettings')->__('tile')),
        );
    }

}
