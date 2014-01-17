<?php

class AHT_Customerpictures_Model_Mysql4_Users extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the customerpictures_id refers to the key field in your database table.
        $this->_init('customerpictures/users', 'user_id');
		$this->_isPkAutoIncrement = false;
    }
}