<?php
class Magestore_Fontmanagement_Block_Fontmanagement extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getFontmanagement()     
     { 
        if (!$this->hasData('fontmanagement')) {
            $this->setData('fontmanagement', Mage::registry('fontmanagement'));
        }
        return $this->getData('fontmanagement');
        
    }
}?>