<?php
class Mage_Qrange_Block_Qrange extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getQrange()     
     { 
        if (!$this->hasData('qrange')) {
            $this->setData('qrange', Mage::registry('qrange'));
        }
        return $this->getData('qrange');
        
    }
}