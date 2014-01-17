<?php

class Magestore_Gallery_Model_Project extends Varien_Object
{
    const PROJECT_OTHERS	= 'others';
    const PROJECT_JEEP_TJ	= 'jeep_tj';
    const PROJECT_TACOMA	= 'tacoma';
    const PROJECT_JEEP_JK	= 'jeep_jk';

    static public function getOptionArray()
    {
        return array(
            self::PROJECT_OTHERS    => Mage::helper('gallery')->__('Others'),
            self::PROJECT_JEEP_TJ    => Mage::helper('gallery')->__('Jeep TJ'),
            self::PROJECT_TACOMA    => Mage::helper('gallery')->__('Tacoma'),
            self::PROJECT_JEEP_JK    => Mage::helper('gallery')->__('Jeep JK')
        );
    }
}