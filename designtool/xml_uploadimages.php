<?php			
require '../app/Mage.php';
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);

Mage::app();
	 
Mage::getSingleton("core/session", array("name" => "frontend"));
$session = Mage::getSingleton("customer/session");

if($session->isLoggedIn())
{	

	$customer_id = $session->getCustomerId();
	$imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	$model  = Mage::getModel('design/userimage')->load($customer_id,'customer_id');
	$collection = Mage::getModel('design/userimage')->getCollection();
	$collection->addFieldToFilter('customer_id',$customer_id);

	$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allImages>';
	foreach( $collection as $row )
	{		
		$xmlString .= '<Image>';
		$xmlString .= '<id>'.$row['id'].'</id>';
		$xmlString .= '<name>'.$imageUrl.'designtool/uploads/'.$row['imgname'].'</name>';
		if($row['vectorname']!=''):
		$xmlString .= '<vectorname>'.$imageUrl.'designtool/uploads/'.$row['vectorname'].'</vectorname>';
		else:
		$xmlString .= '<vectorname></vectorname>';
		endif;
		$xmlString .= '</Image>';		
	}
	$xmlString .= '</allImages>';
}
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;
?>
