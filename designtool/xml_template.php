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
$collection = Mage::getModel('gallery/gallery')->getCollection()->AddFieldToFilter('album_id',$cid)->AddFieldToFilter('status', 1);


 
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><allTemplate>';
foreach($collection as $res)		
{
	$xmlString .= '	<Template>';
	$xmlString .= '	<TemplateName>'.htmlspecialchars($res->getTitle()).'</TemplateName>';
	$xmlString .= '	<TemplateID>'.$res->getGallery_id().'</TemplateID>';
	//$xmlString .= '	<imagePath>'.$path.'designtool/saveimg/'.$res->getFilename().'</imagePath>';
	$xmlString .= '	<imagePath>'.$path.'media/'.$res->getFilename().'</imagePath>';		
	$xmlString .= '	<xmldata>'.$res->getDesigndata().'</xmldata>';
	$xmlString .= '	</Template>';	
}

$xmlString .= '</allTemplate>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;

?>