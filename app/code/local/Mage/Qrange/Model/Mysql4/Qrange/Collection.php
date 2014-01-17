<?php

class Mage_Qrange_Model_Mysql4_Qrange_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('qrange/qrange');
    }
}