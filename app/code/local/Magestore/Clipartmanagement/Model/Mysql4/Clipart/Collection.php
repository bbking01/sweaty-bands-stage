<?php

class Magestore_Clipartmanagement_Model_Mysql4_Clipart_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
       // parent::_construct();
        $this->_init('clipartmanagement/clipart');
    }	
	
	public function toOptionArray()	
    {	
		return parent::_toOptionArray('clipart_id', 'clipart_image' );
    }
}?>