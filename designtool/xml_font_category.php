<?php	
require '../app/Mage.php';
Mage::app();		
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
/*Code updated by bhagyashri to show only enable font category started*/
$collection = Mage::getModel('fontmanagement/fontcategory')->getCollection()->AddFieldToFilter('status', 1);
/*Code updated by bhagyashri to show only enable font category ended*/
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><fontCats>';
foreach($collection as $res)		
{
	$xmlString .= '	<fontcategory>';
	$xmlString .= '	<name>'.$res->getCategory_name().'</name>';
	$xmlString .= '	<fontCatID>'.$res->getFont_cat_id().'</fontCatID>';
	$xmlString .= '	</fontcategory>';			
}
$xmlString .= '</fontCats>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;

?>