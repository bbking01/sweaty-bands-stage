<?php
class AHT_Customerpictures_Block_Customerpictures extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getCustomerpictures()     
     { 
        if (!$this->hasData('customerpictures')) {
            $this->setData('customerpictures', Mage::registry('customerpictures'));
        }
        return $this->getData('customerpictures');
        
    }
}