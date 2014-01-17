<?php			
require '../app/Mage.php';		
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
if(isset($_GET['cid']))
{
	$cid = $_GET['cid'];	
	$filters = array('c_category_id '=>$cid);
}else
{
	$filters = '';
}
$collection = Mage::getModel('clipartmanagement/clipart')->getCollection()->AddFieldToFilter('c_category_id',$cid)->AddFieldToFilter('status', 1);
$collection->setOrder("position", "ASC");

 
/*echo '<pre>';
print_r($collection->getData());
echo '</pre>';
exit;*/

 
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allArt>';
foreach($collection as $res)		
{
	$xmlString .= '	<art>';
	$xmlString .= '	<artName>'.$res->getClipart_name().'</artName>';
	$xmlString .= '	<artID>'.$res->getClipart_id().'</artID>';
	$xmlString .= '	<parentID>'.$res->getC_category_id().'</parentID>';
	$xmlString .= '	<thumbPath>'.$path.'/media/clipart/images/thumb/'.$res->getClipart_image().'</thumbPath>';		
	$xmlString .= '	<imagePath>'.$path.'/media/clipart/images/'.$res->getClipart_image().'</imagePath>';
	$xmlString .= '	<status>'.$res->getStatus().'</status>';
	$xmlString .= '	<position>'.$res->getPosition().'</position>';
	$xmlString .= '	<type>'.'art'.'</type>';
	$xmlString .= '	</art>';	
}

$xmlString .= '</allArt>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;

?>