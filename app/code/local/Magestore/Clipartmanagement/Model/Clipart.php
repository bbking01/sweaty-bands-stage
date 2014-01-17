<?php

class Magestore_Clipartmanagement_Model_Clipart extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('clipartmanagement/clipart');
    }
}