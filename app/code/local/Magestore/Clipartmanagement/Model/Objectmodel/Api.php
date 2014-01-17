<?php
class Magestore_Clipartmanagement_Model_Objectmodel_Api extends Mage_Api_Model_Resource_Abstract
{    	
	public function items( $filters )
	{		
	    $collection = Mage::getModel('clipartmanagement/clipart')->getCollection();
		if (is_array($filters)) {
			try {
				foreach ($filters as $field => $value) {
					$collection->addFieldToFilter($field, $value);
				}
			} catch (Mage_Core_Exception $e) {				
				$this->_fault('filters_invalid', $e->getMessage());				
			}
		}else		
		{				
			$collection->load();
		}
				
		$result = array();
		foreach ($collection as $customer) {
			$result[] = $customer->toArray();
		}
		//$result = 'hi';
		return $result;
		
	}    
	
	public function category_items( $filters )
	{		
	    $collection = Mage::getModel('clipartmanagement/clipartcategory')->getCollection();
		if (is_array($filters)) {
			try {
				foreach ($filters as $field => $value) {
					$collection->addFieldToFilter($field, $value);
				}
			} catch (Mage_Core_Exception $e) {				
				$this->_fault('filters_invalid', $e->getMessage());				
			}
		}else		
		{				
			$collection->load();
		}
				
		$result = array();
		foreach ($collection as $customer) {
			$result[] = $customer->toArray();
		}
		//$result = 'hi';
		return $result;
		
	} 
}
?>