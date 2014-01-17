<?php
/**
 * @copyright   Copyright (c) 2009-2012 Amasty (http://www.amasty.com)
 */ 
class Amasty_Perm_Block_Description extends Mage_Core_Block_Template
{    
	public  function getDealer()
	{
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
			
			$placement = $this->getPlacement();
			if (Mage::getStoreConfig('amperm/general/description_' . $placement) == 0) {
				return; 
			}
			
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getEntityId();
            $dealerId = Mage::getModel('amperm/perm')->getResource()->getUserByCustomer($customerId);
            $dealer = Mage::getModel('admin/user')->load($dealerId);
            return $dealer;
        }
	}
}

