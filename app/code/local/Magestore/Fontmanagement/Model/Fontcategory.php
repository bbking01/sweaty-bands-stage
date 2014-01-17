<?php

class Magestore_Fontmanagement_Model_Fontcategory extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('fontmanagement/fontcategory');
    }
	
	public function getCategoryCollection()
    {
		
        return Mage::getResourceModel('fontmanagement/fontcategory_collection');
    }
}