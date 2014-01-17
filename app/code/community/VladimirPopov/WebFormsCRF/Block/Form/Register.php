<?php
class VladimirPopov_WebFormsCRF_Block_Form_Register extends Mage_Customer_Block_Form_Register{
	
	public function getTemplate(){
		if(Mage::getStoreConfig('webformscrf/registration/enable') && Mage::getStoreConfig('webformscrf/registration/form')) return;
		return parent::getTemplate();
	}
}
?>
