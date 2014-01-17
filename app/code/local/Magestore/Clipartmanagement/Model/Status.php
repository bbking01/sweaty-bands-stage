<?php

class Magestore_Clipartmanagement_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('clipartmanagement')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('clipartmanagement')->__('Disabled')
        );
    }
}