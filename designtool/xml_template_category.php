<?php			
require '../app/Mage.php';		
Mage::app();
$path = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$collection = Mage::getModel('gallery/album')->getCollection()->AddFieldToFilter('status', 1);
$xmlString = '<?xml version="1.0" encoding="iso-8859-1"?><TemplateCategory>';
foreach($collection as $res)		
{
	$xmlString .= '	<category>';
	$xmlString .= '	<catName>'.$res->getTitle().'</catName>';
	$xmlString .= '	<catID>'.$res->getAlbum_id().'</catID>';
	$xmlString .= '	<imagePath>'.$path.'media/'.$res->getFilename().'</imagePath>';	
	$xmlString .= '	</category>';			
}
$xmlString .= '</TemplateCategory>';
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-type: text/xml");
header ("Content-Description: PHP/INTERBASE Generated Data" );
echo $xmlString;

?>