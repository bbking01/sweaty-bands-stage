<?php

class Magestore_Fontmanagement_Model_Addfont extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fontmanagement/addfont');
    }
}