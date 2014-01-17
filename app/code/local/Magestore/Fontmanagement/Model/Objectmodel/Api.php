<?php
class Magestore_Fontmanagement_Model_Objectmodel_Api extends Mage_Api_Model_Resource_Abstract
{
     /**
     * method Name
     *
     * @param string $orderIncrementId
     * @return string
     */
    public function methodName( $arg )
    {
       // Mage::log("Companyname_Modulename_Model_Objectmodel_Api: methodName called");
        $result = "hello world! My Argument is " . $arg;
        return $result;
	}
	
	public function items( $filters )
	{		
	    $collection = Mage::getModel('fontmanagement/addfont')->getCollection();
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
	    $collection = Mage::getModel('fontmanagement/fontcategory')->getCollection();
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