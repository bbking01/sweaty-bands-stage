<?php

class Magestore_Clipartmanagement_Model_Clipartcategory extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('clipartmanagement/clipartcategory');
    }
	
	public function getCategoryCollection()
    {
		
        return Mage::getResourceModel('clipartmanagement/clipartcategory_collection');
    }
}