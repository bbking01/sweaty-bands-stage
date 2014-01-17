<?php 
	require '../app/Mage.php';
	Mage::app();
	$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

		$name =(float) Mage::getStoreConfig('sales/backend/name_price');							
		$number = (float)Mage::getStoreConfig('sales/backend/number_price');	
		$symbol =  Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(); 		
	echo 'nameprice='.$name.'&numberprice='.$number.'&sign='.$symbol;
?>