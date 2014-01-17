<?php
class Mage_Colors_Block_Colors extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getColors()     
     { 
        if (!$this->hasData('colors')) {
            $this->setData('colors', Mage::registry('colors'));
        }
        return $this->getData('colors');
        
    }
}