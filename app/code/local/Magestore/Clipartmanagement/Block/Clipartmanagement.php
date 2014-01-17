<?php
class Magestore_Clipartmanagement_Block_Clipartmanagement extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getClipartmanagement()     
     { 
        if (!$this->hasData('clipartmanagement')) {
            $this->setData('clipartmanagement', Mage::registry('clipartmanagement'));
        }
        return $this->getData('clipartmanagement');
        
    }
}?>