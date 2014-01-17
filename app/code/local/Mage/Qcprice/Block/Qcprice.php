<?php
class Mage_Qcprice_Block_Qcprice extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getQcprice()     
     { 
        if (!$this->hasData('qcprice')) {
            $this->setData('qcprice', Mage::registry('qcprice'));
        }
        return $this->getData('qcprice');
        
    }
}