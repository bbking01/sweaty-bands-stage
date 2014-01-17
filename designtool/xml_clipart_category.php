<?php			
require '../app/Mage.php';		
Mage::app();
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
/*Code updated by bhagyashri to show only enable clipart category started*/
$collection = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->AddFieldToFilter('parent_cat_id',0)->AddFieldToFilter('status', 1);
$collection->setOrder("position", "ASC");

/*Code updated by bhagyashri to show only enable clipart category ended*/
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><artCategory>';

foreach($collection as $res)		
{

	$collection1 = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->AddFieldToFilter('parent_cat_id',$res->getClipart_cat_id())->AddFieldToFilter('status', 1);
	
//print_r($res->getData());exit;
	$xmlString .= '	<category>';
	$xmlString .= '	<catName>'.$res->getCategory_name().'</catName>';
	$xmlString .= '	<catID>'.$res->getClipart_cat_id().'</catID>';
	$xmlString .= '	<position>'.$res->getPosition().'</position>';	
	$xmlString .= '	<type>'.'category'.'</type>';
	$xmlString .= '	</category>';			
}
$xmlString .= '</artCategory>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;

?>