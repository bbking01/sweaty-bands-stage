<?php

class Magestore_Printcolormanagement_Model_Mysql4_Printcolormanagement_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
       // parent::_construct();
        $this->_init('printcolormanagement/printcolormanagement');
    }
	
	/*public function toOptionArray()	
    {	
		return parent::_toOptionArray('font_image', 'font_file' );
    }
	
	public function toOptionIdArray()	
    {	
		return parent::_toOptionArray('font_id', 'font_name' );
    }*/
}?>