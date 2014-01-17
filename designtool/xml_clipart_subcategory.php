<?php			
require '../app/Mage.php';		
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
//$proxy = new SoapClient($path.'api/soap?wsdl');
//$sessionId = $proxy->login('naincy', '123456');
if(isset($_GET['cid']))
{
	$cid = $_GET['cid'];	
}else
{
	$cid = 0;
}
$collection = Mage::getModel('clipartmanagement/clipartcategory')->getCollection()->AddFieldToFilter('parent_cat_id',$cid)
->AddFieldToFilter('status', 1);

 
/*echo '<pre>';
print_r($collection->getData());
echo '</pre>';
exit;*/


$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><artSubCategory>';
foreach($collection as $res)		
{
	$xmlString .= '	<subCategory>';
	$xmlString .= '	<subCatName>'.$res->getCategory_name().'</subCatName>';
	$xmlString .= '	<subCatID>'.$res->getClipart_cat_id().'</subCatID>';
	$xmlString .= '	<parentID>'.$res->getParent_cat_id().'</parentID>';
	$xmlString .= '	<position>'.$res->getPosition().'</position>';	
	$xmlString .= '	<type>'.'subcategory'.'</type>';
	$xmlString .= '	</subCategory>';			
}

$xmlString .= '</artSubCategory>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;

?>