<?php

class Mage_Qrange_Model_Mysql4_Qrange extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the qrange_id refers to the key field in your database table.
        $this->_init('qrange/qrange', 'qrange_id');
    }
}