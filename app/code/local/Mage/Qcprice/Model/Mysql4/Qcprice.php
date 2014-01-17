<?php

class Mage_Qcprice_Model_Mysql4_Qcprice extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the qcprice_id refers to the key field in your database table.
        $this->_init('qcprice/qcprice', 'qcprice_id');
    }
}