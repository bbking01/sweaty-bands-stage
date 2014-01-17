<?php

class Magestore_Clipartmanagement_Model_Mysql4_Clipartcategory_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
       // parent::_construct();
        $this->_init('clipartmanagement/clipartcategory');
	
    }
	
	public function toOptionArray($disabled = 0)	
    {			
		$d = '';
		if($disabled == 1)
		{
			$d['disabled_cat'] = 'true';
		}		
		return parent::_toOptionArray('clipart_cat_id', 'category_name', $d);
    }
	
	public function _toOptionHash($valueField='clipart_cat_id', $labelField='category_name')
	{
	  return parent::_toOptionHash($valueField, $labelField);
	}
}?>