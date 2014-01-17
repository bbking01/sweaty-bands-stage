<?php

class AHT_Customerpictures_Model_Images extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerpictures/images');
    }
}