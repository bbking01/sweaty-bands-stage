<?php
require '../app/Mage.php';
Mage::app();
$facebook = Mage::getStoreConfig('designtool_options/setup_app_config/facebook');
$flickr = Mage::getStoreConfig('designtool_options/setup_app_config/flickr');
$namePrice =(float) Mage::getStoreConfig('designtool_options/name_number_price/name_price');	
$numberPrice = (float)Mage::getStoreConfig('designtool_options/name_number_price/number_price');		
$symbol =  Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol(); 	
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><configuration>';
$xmlString .= '	<facebook>'.$facebook.'</facebook>';
$xmlString .= '	<flickr>'.$flickr.'</flickr>';
$xmlString .= '	<nameprice>'.Mage::helper('core')->currency($namePrice,true,false).'</nameprice>';
$xmlString .= '	<numberprice>'.Mage::helper('core')->currency($numberPrice,true,false).'</numberprice>';
$xmlString .= '	<sign>'.$symbol.'</sign>';
$xmlString .= '</configuration>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;
?>