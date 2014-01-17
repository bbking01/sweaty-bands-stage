<?php

class Magestore_Fontmanagement_Model_Mysql4_Fontcategory_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
       // parent::_construct();
        $this->_init('fontmanagement/fontcategory');
	
    }
	
	public function toOptionArray()
    {
        return parent::_toOptionArray('font_cat_id', 'category_name');
    }
	
	public function _toOptionHash($valueField='font_cat_id', $labelField='category_name')
	{
	  return parent::_toOptionHash($valueField, $labelField);
	}
}?>