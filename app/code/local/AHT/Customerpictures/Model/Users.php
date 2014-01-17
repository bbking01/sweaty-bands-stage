<?php

class AHT_Customerpictures_Model_Users extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('customerpictures/users');
    }
}